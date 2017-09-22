<?php

namespace pxgamer\Torrent;

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
    public $data;

    /**
     * Torrent constructor.
     *
     * @param int $piece_length
     * @throws \Exception
     */
    public function __construct($piece_length)
    {
        assert($piece_length >= self::BLOCK_SIZE, new \Exception);
        assert($piece_length, new \Exception);

        $this->piece_length = $piece_length;
        $this->piece_layers = []; // v2 piece hashes
        $this->pieces = []; //v1 piece hashes
        $this->files = [];
        $this->info = [];
    }

    public function prepare($path)
    {
        $this->base_path = realpath($path);
        $this->name = basename($path);

        if (is_file($this->base_path)) {
            $this->file_tree = [
                $this->name => $this->walk_path($this->base_path)
            ];

            $this->files = [];
            $this->length = $this->file_tree[$this->name]['']['length'];
        } else {
            $this->file_tree = $this->walk_path($this->base_path);
        }

        try {
            if (count($this->files) > 1) {
                $this->pieces[] = $this->residue_hasher->append_padding();
                $this->files[] = [
                    'attr'   => 'p',
                    'length' => $this->residue_hasher->pad_length,
                    'path'   => [
                        '.pad',
                        (string)$this->residue_hasher->pad_length
                    ]
                ];
            } else {
                $this->pieces[] = $this->residue_hasher->discard_padding();
            }

            $this->residue_hasher = null;
        } catch (\Exception $e) {

        }

        // Flatten the piece hashes into a single bytes object
        $this->pieces = '';

        $this->base_path = null;
    }

    /**
     * Return the info hash in v2 (SHA256) format
     *
     * @return string
     * @throws \Exception
     */
    public function info_hash_v2()
    {
        return hash('sha256', Bencode::encode($this->info));
    }

    /**
     * Return the info hash in v1 (SHA1) format
     *
     * @return string
     * @throws \Exception
     */
    public function info_hash_v1()
    {
        return hash('sha1', Bencode::encode($this->info));
    }

    /**
     * Walk through a directory or file and return an object
     *
     * @param string $path
     * @return array
     * @throws \Exception
     */
    private function walk_path($path)
    {
        if (file_exists($path)) {
            try {
                $this->pieces[] = $this->residue_hasher->append_padding();
                $this->files[] = [
                    'attr'   => 'p',
                    'length' => $this->residue_hasher->pad_length,
                    'path'   => [
                        '.pad',
                        (string)$this->residue_hasher->pad_length
                    ]
                ];

                unset($this->residue_hasher);
            } catch (\Error $e) {

            }

            $hashes = new FileHasher($path, $this->piece_length);
            $this->residue_hasher = $hashes;
            $this->piece_layers[] = $hashes;
            $this->pieces = array_merge($this->pieces, $hashes->piecesv1);
            $this->files[] = [
                'length' => $hashes->length,
                'path'   => explode(DIRECTORY_SEPARATOR, substr($this->base_path, strlen($path)))
            ];

            if (count($hashes) == 0) {
                return [
                    '' => [
                        'length' => count($hashes)
                    ]
                ];
            } else {
                return [
                    '' => [
                        'length'      => count($hashes),
                        'pieces root' => pack('H*', $hashes->root)
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

            return $list;
        }

        throw new \Exception('Unsupported dentry type');
    }

    /**
     * Create an object of the v2 metadata
     *
     * @param string $tracker
     * @param bool   $hybrid
     * @return array
     */
    public function create($tracker, $hybrid = true)
    {
        $info = [
            'name'         => $this->name,
            'piece length' => $this->piece_length,
            'file tree'    => $this->file_tree,
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

        return $this->data = [
            'announce'     => $tracker,
            'info'         => $info,
            'piece layers' => $layers
        ];
    }

    /**
     * @param null|string $filename
     * @return bool|int
     * @throws \Exception
     */
    public function save($filename = null)
    {
        return file_put_contents(
            is_null($filename) ?
                $this->info['name'] . '.torrent' :
                $filename,
            Bencode::encode($this->data)
        );
    }
}