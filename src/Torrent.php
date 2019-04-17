<?php

namespace pxgamer\Torrent;

/**
 * Class Torrent
 */
final class Torrent
{
    /** Block size */
    public const BLOCK_SIZE = 16384; // 16KB

    /** @var int */
    public $pieceLength;
    /** @var array */
    public $pieceLayers;
    /** @var array */
    public $files;
    /** @var array */
    public $info;
    /** @var string */
    public $basePath;
    /** @var string */
    public $name;
    /** @var array */
    public $pieces;
    /** @var array */
    public $fileTree;
    /** @var int */
    public $length;
    /** @var FileHasher */
    public $residueHasher;
    /** @var array */
    public $data;

    /**
     * Torrent constructor
     *
     * @param int $pieceLength
     * @throws \Exception
     */
    public function __construct(int $pieceLength)
    {
        assert($pieceLength >= self::BLOCK_SIZE, new \Exception());
        assert($pieceLength, new \Exception());

        $this->pieceLength = $pieceLength;
        $this->pieceLayers = []; // v2 piece hashes
        $this->pieces = []; // v1 piece hashes
        $this->files = [];
        $this->info = [];
    }

    /**
     * @param string $path
     * @throws \Exception
     */
    public function prepare(string $path): void
    {
        $this->basePath = realpath($path);
        $this->name = basename($path);

        if (is_file($this->basePath)) {
            $this->fileTree = [
                $this->name => $this->walkPath($this->basePath),
            ];

            $this->files = [];
            $this->length = $this->fileTree[$this->name]['']['length'];
        } else {
            $this->fileTree = $this->walkPath($this->basePath);
        }

        try {
            if (count($this->files) > 1) {
                $this->pieces[] = $this->residueHasher->appendPadding();
                $this->files[] = [
                    'attr' => 'p',
                    'length' => $this->residueHasher->padLength,
                    'path' => [
                        '.pad',
                        (string)$this->residueHasher->padLength,
                    ],
                ];
            } else {
                $this->pieces[] = $this->residueHasher->discardPadding();
            }

            $this->residueHasher = null;
        } catch (\Exception $e) {
        }

        // Flatten the piece hashes into a single bytes object
        $this->pieces = '';

        $this->basePath = null;
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
    private function walkPath(string $path): array
    {
        if (file_exists($path)) {
            try {
                $this->pieces[] = $this->residueHasher->appendPadding();
                $this->files[] = [
                    'attr' => 'p',
                    'length' => $this->residueHasher->padLength,
                    'path' => [
                        '.pad',
                        (string)$this->residueHasher->padLength,
                    ],
                ];

                unset($this->residueHasher);
            } catch (\Error $e) {
            }

            $hashes = new FileHasher($path, $this->pieceLength);
            $this->residueHasher = $hashes;
            $this->pieceLayers[] = $hashes;
            $this->pieces = array_merge($this->pieces, $hashes->v1Pieces);
            $this->files[] = [
                'length' => $hashes->length,
                'path' => explode(DIRECTORY_SEPARATOR, substr($this->basePath, strlen($path))),
            ];

            if ($hashes->length === 0) {
                return [
                    '' => [
                        'length' => $hashes->length,
                    ],
                ];
            }

            return [
                '' => [
                    'length' => $hashes->length,
                    'pieces root' => pack('H*', $hashes->root),
                ],
            ];
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
    public function create(string $tracker, bool $hybrid = true): array
    {
        $info = [
            'name' => $this->name,
            'piece length' => $this->pieceLength,
            'file tree' => $this->fileTree,
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
        foreach ($this->pieceLayers as $f) {
            if ($f->length > $this->pieceLength) {
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
    public function save(?string $filename = null)
    {
        return file_put_contents(
            $filename ?? $this->info['name'].'.torrent',
            Bencode::encode($this->data)
        );
    }
}
