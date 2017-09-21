<?php

namespace pxgamer\Torrent;

use PureBencode\Bencode;

/**
 * Class Torrent
 * @package pxgamer\Torrent
 */
class Torrent
{
    public const BLOCK_SIZE = 2 ** 14; // 16KB

    public $piece_length;
    public $piece_layers;
    public $files;
    public $info;
    public $base_path;
    public $name;
    public $pieces;
    public $file_tree;
    public $length;
    /**
     * @var FileHasher
     */
    public $residue_hasher;

    /**
     * Torrent constructor.
     *
     * @param string $path
     * @param int $piece_length
     */
    public function __construct($path, $piece_length)
    {
        assert($piece_length >= self::BLOCK_SIZE, new \Exception);
        assert($piece_length, new \Exception);

        $this->piece_length = $piece_length;
        $this->name = basename($path);
        $this->piece_layers = []; // v2 piece hashes
        $this->pieces = []; //v1 piece hashes
        $this->files = [];
        $this->info = [];

        $this->base_path = realpath($path);

        if (is_file($this->base_path)) {
            $this->file_tree = (object)[
                $this->name => $this->walk_path($this->base_path)
            ];

            unset($this->files);
            $this->length = $this->file_tree[$this->name][b''][b'length'];
        } else {
            $this->file_tree = $this->walk_path($this->base_path);
        }

        try {
            if (count($this->files) > 1) {
                $this->pieces[] = $this->residue_hasher->append_padding();
                $this->files[] = (object)[
                    'attr' => 'p',
                    'length' => $this->residue_hasher->pad_length,
                    'path' => [
                        '.pad',
                        (string)$this->residue_hasher->pad_length
                    ]
                ];
            } else {
                $this->pieces[] = $this->residue_hasher->discard_padding();
            }
            unset($this->residue_hasher);
        } catch (\Exception $e) {

        }

        // Flatten the piece hashes into a single bytes object
        $this->pieces = '';

        unset($this->base_path);
    }

    /**
     * Return the info hash in v2 (SHA256) format
     *
     * @return string
     */
    public function info_hash_v2()
    {
        return hash('sha256', Bencode::encode($this->info));
    }

    /**
     * Return the info hash in v1 (SHA1) format
     *
     * @return string
     */
    public function info_hash_v1()
    {
        return hash('sha1', Bencode::encode($this->info));
    }

    /**
     * Walk through a directory or file and return an object
     *
     * @param string $path
     * @return object
     * @throws \Exception
     */
    private function walk_path($path)
    {
        if (file_exists($path)) {
            try {
                $this->pieces[] = $this->residue_hasher->append_padding();
                $this->files[] = (object)[
                    'attr' => 'p',
                    'length' => $this->residue_hasher->pad_length,
                    'path' => [
                        '.pad',
                        (string)$this->residue_hasher->pad_length
                    ]
                ];

                unset($this->residue_hasher);
            } catch (\Exception $e) {

            }

            $hashes = new FileHasher($path, $this->piece_length);
            $this->residue_hasher = $hashes;
            $this->piece_layers[] = $hashes;
            $this->pieces = array_merge($this->pieces, $hashes->piecesv1);
            $this->files[] = (object)[
                'length' => $hashes->length,
                'path' => explode(DIRECTORY_SEPARATOR, substr($this->base_path, strlen($path)))
            ];

            if (count($hashes) == 0) {
                return (object)[
                    '' => (object)[
                        'length' => count($hashes)
                    ]
                ];
            } else {
                return (object)[
                    '' => (object)[
                        'length' => count($hashes),
                        'pieces root' => $hashes->root
                    ]
                ];
            }
        }

        if (is_dir($path)) {
            // Generate a lexicographic object populated of files matching the bencoded order
            $dentries = [];
            $list = [];

            foreach (scandir($path) as $p) {
                $dentries[] = [$p->name, $p->path];
            }

            sort($dentries);

            foreach ($dentries as $p) {
                $list[$p[0]] = $this->walk_path($p[1]);
            }

            return (object)$list;
        }

        throw new \Exception('Unsupported dentry type');
    }

    /**
     * Create an object of the v2 metadata
     *
     * @param string $tracker
     * @param bool $hybrid
     * @return object
     */
    public function create($tracker, $hybrid = true)
    {
        $info = (object)[
            'name' => $this->name,
            'piece length' => $this->piece_length,
            'file tree' => $this->file_tree,
            'meta version' => 2,
        ];

        if ($hybrid) {
            $info['pieces'] = $this->pieces;

            try {
                $info['files'] = $this->files;
            } catch (\Exception $e) {
                $info['length'] = $this->length;
            }
        }

        $this->info = $info;

        $layers = [];
        foreach ($this->piece_layers as $f) {
            if ($f->length > $this->piece_length) {
                $layers[$f->root] = $f->piecesv2;
            }
        }

        return (object)[
            'announce' => $tracker,
            'info' => $info,
            'piece layers' => (object)$layers
        ];
    }
}