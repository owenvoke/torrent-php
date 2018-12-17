<?php

namespace pxgamer\Torrent;

/**
 * Class Torrent
 */
class Torrent
{
    /**
     * Block size
     */
    public const BLOCK_SIZE = 16384; // 16KB

    /**
     * @var int
     */
    public $piece_length;
    /**
     * @var array
     */
    public $piece_layers;
    /**
     * @var array
     */
    public $files;
    /**
     * @var array
     */
    public $info;
    /**
     * @var string
     */
    public $base_path;
    /**
     * @var string
     */
    public $name;
    /**
     * @var array
     */
    public $pieces;
    /**
     * @var array
     */
    public $file_tree;
    /**
     * @var int
     */
    public $length;
    /**
     * @var FileHasher
     */
    public $residue_hasher;
    /**
     * @var array
     */
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

    /**
     * @param string $path
     * @throws \Exception
     */
    public function prepare($path): void
    {
        $this->base_path = realpath($path);
        $this->name = basename($path);

        if (is_file($this->base_path)) {
            $this->file_tree = [
                $this->name => $this->walkPath($this->base_path),
            ];

            $this->files = [];
            $this->length = $this->file_tree[$this->name]['']['length'];
        } else {
            $this->file_tree = $this->walkPath($this->base_path);
        }

        try {
            if (count($this->files) > 1) {
                $this->pieces[] = $this->residue_hasher->appendPadding();
                $this->files[] = [
                    'attr' => 'p',
                    'length' => $this->residue_hasher->padLength,
                    'path' => [
                        '.pad',
                        (string)$this->residue_hasher->padLength,
                    ],
                ];
            } else {
                $this->pieces[] = $this->residue_hasher->discardPadding();
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
    public function infoHashV2(): string
    {
        return hash('sha256', Bencode::encode($this->info));
    }

    /**
     * Return the info hash in v1 (SHA1) format
     *
     * @return string
     * @throws \Exception
     */
    public function infoHashV1(): string
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
    private function walkPath($path): array
    {
        if (file_exists($path)) {
            try {
                $this->pieces[] = $this->residue_hasher->appendPadding();
                $this->files[] = [
                    'attr' => 'p',
                    'length' => $this->residue_hasher->padLength,
                    'path' => [
                        '.pad',
                        (string)$this->residue_hasher->padLength,
                    ],
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
                'path' => explode(DIRECTORY_SEPARATOR, substr($this->base_path, strlen($path))),
            ];

            if ($hashes->length == 0) {
                return [
                    '' => [
                        'length' => $hashes->length,
                    ],
                ];
            } else {
                return [
                    '' => [
                        'length' => $hashes->length,
                        'pieces root' => pack('H*', $hashes->root),
                    ],
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
                $list[$p[0]] = $this->walkPath($p[1]);
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
    public function create($tracker, $hybrid = true): array
    {
        $info = [
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

        return $this->data = [
            'announce' => $tracker,
            'info' => $info,
            'piece layers' => $layers,
        ];
    }

    /**
     * Save the data to a .torrent file
     *
     * @param null|string $filename
     * @return bool|int
     * @throws \Exception
     */
    public function save($filename = null)
    {
        return file_put_contents(
            is_null($filename) ?
                $this->info['name'].'.torrent' :
                $filename,
            Bencode::encode($this->data)
        );
    }
}
