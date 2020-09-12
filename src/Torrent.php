<?php

namespace OwenVoke\Torrent;

use Error;
use Exception;

final class Torrent
{
    /** @var int */
    public const BLOCK_SIZE = 16_384; // 16KB
    // 16KB
    // 16KB
    // 16KB
    /** @var int */
    public int $pieceLength;
    public array $pieceLayers;
    public array $files;
    public array $info;
    /** @var string */
    public $basePath;
    public ?string $name = null;
    /** @var array */
    public $pieces;
    public ?array $fileTree = null;
    public int $length;
    public ?FileHasher $residueHasher = null;
    public ?array $data = null;
    /**
     * @var string
     */
    private const LENGTH = 'length';
    /**
     * @var string
     */
    private const PATH = 'path';

    /**
     * Torrent constructor.
     *
     * @param int $pieceLength
     * @throws Exception
     */
    public function __construct(int $pieceLength)
    {
        assert($pieceLength >= self::BLOCK_SIZE, new Exception());
        assert($pieceLength, new Exception());

        $this->pieceLength = $pieceLength;
        $this->pieceLayers = []; // v2 piece hashes
        $this->pieces = []; // v1 piece hashes
        $this->files = [];
        $this->info = [];
    }

    /**
     * @param string $path
     * @throws Exception
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
            $this->length = $this->fileTree[$this->name][''][self::LENGTH];
        } else {
            $this->fileTree = $this->walkPath($this->basePath);
        }

        try {
            if ((is_countable($this->files) ? count($this->files) : 0) > 1) {
                $this->pieces[] = $this->residueHasher->appendPadding();
                $this->files[] = [
                    'attr' => 'p',
                    self::LENGTH => $this->residueHasher->padLength,
                    self::PATH => [
                        '.pad',
                        (string) $this->residueHasher->padLength,
                    ],
                ];
            } else {
                $this->pieces[] = $this->residueHasher->discardPadding();
            }

            $this->residueHasher = null;
        } catch (Exception $e) {
        }

        // Flatten the piece hashes into a single bytes object
        $this->pieces = '';

        $this->basePath = null;
    }

    /**
     * Return the info hash in v2 (SHA256) format.
     *
     * @return string
     * @throws Exception
     */
    public function infoHashV2(): string
    {
        return hash('sha256', Bencode::encode($this->info));
    }

    /**
     * Return the info hash in v1 (SHA1) format.
     *
     * @return string
     * @throws Exception
     */
    public function infoHashV1(): string
    {
        return hash('sha1', Bencode::encode($this->info));
    }

    /**
     * Walk through a directory or file and return an object.
     *
     * @param string $path
     * @return array
     * @throws Exception
     */
    private function walkPath(string $path): array
    {
        if (file_exists($path)) {
            try {
                $this->pieces[] = $this->residueHasher->appendPadding();
                $this->files[] = [
                    'attr' => 'p',
                    self::LENGTH => $this->residueHasher->padLength,
                    self::PATH => [
                        '.pad',
                        (string) $this->residueHasher->padLength,
                    ],
                ];

                unset($this->residueHasher);
            } catch (Error $e) {
            }

            $hashes = new FileHasher($path, $this->pieceLength);
            $this->residueHasher = $hashes;
            $this->pieceLayers[] = $hashes;
            $this->pieces = array_merge($this->pieces, $hashes->v1Pieces);
            $this->files[] = [
                self::LENGTH => $hashes->length,
                self::PATH => explode(DIRECTORY_SEPARATOR, substr($this->basePath, strlen($path))),
            ];

            if ($hashes->length === 0) {
                return [
                    '' => [
                        self::LENGTH => $hashes->length,
                    ],
                ];
            }

            return [
                '' => [
                    self::LENGTH => $hashes->length,
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

        throw new Exception('Unsupported dentry type');
    }

    /**
     * Create an object of the v2 metadata.
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
            } catch (Exception $e) {
                $info[self::LENGTH] = $this->length;
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
     * Save the data to a .torrent file.
     *
     * @param null|string $filename
     * @return bool|int
     * @throws Exception
     */
    public function save(?string $filename = null)
    {
        return file_put_contents(
            $filename ?? $this->info['name'].'.torrent',
            Bencode::encode($this->data)
        );
    }
}
