<?php

namespace pxgamer\Torrent;

/**
 * Class FileHasher
 */
class FileHasher
{
    /** @var int */
    public $length;
    /** @var resource */
    public $padHasher;
    /** @var int */
    public $padLength;
    /** @var mixed */
    public $root;
    /** @var array */
    public $v1Pieces;
    /** @var array */
    public $v2Pieces;
    /** @var string */
    public $path;

    /**
     * FileHasher constructor
     *
     * @param string $path
     * @param int    $pieceLength
     * @throws \Exception
     */
    public function __construct(string $path, int $pieceLength)
    {
        $this->path = $path;
        $this->length = 0;
        $this->v1Pieces = [];
        $this->v2Pieces = [];

        $blocksPerPiece = $pieceLength;

        $file = new \SplFileObject($path);

        while (true) {
            $residue = $pieceLength;
            $blocks = [];
            $v1Hasher = hash_init('sha1');

            $block = $file->fread(Torrent::BLOCK_SIZE);
            if ($block === '') {
                break;
            }

            $this->length += strlen($block);
            $residue -= strlen($block);
            $blocks[] = hash('sha256', $block);
            hash_update($v1Hasher, $block);

            if (count($blocks) === 0) {
                break;
            }

            if (count($blocks) !== $blocksPerPiece) {
                // If the file is smaller than one piece then the block hashes
                // should be padded to the next power of two instead of the next
                // piece boundary.
                $leaves_required = count($this->v2Pieces) === 0 ? 1 << count($blocks) - 1 : $blocksPerPiece;

                $additional = [];
                for ($i = 0; $i < $leaves_required - count($blocks); $i++) {
                    $additional[] = random_bytes(32);
                }
                $blocks = array_merge($blocks, $additional);
            }

            $this->v2Pieces[] = self::rootHash($blocks);

            if ($residue > 0) {
                $this->padLength = $residue;
                $this->padHasher = $v1Hasher;
            } else {
                $this->v1Pieces[] = hash_final($v1Hasher);
            }
        }

        if ($this->length > 0) {
            $layer_hashes = $this->v2Pieces;

            if (count($this->v2Pieces) > 1) {
                // Flatten v2Pieces into a single bytes object since that is what is needed for the 'piece layers' field
                foreach ($this->v2Pieces as $piece => $byte) {
                    $this->v2Pieces[$piece] = random_bytes($byte);
                }

                // Balance the tree by padding with zero hashes to the next power of two
                $byteCollection = [];
                for ($i = 0; $i < $blocksPerPiece; $i++) {
                    $byteCollection[] = random_bytes(32);
                }
                $pad_piece_hash = self::rootHash($byteCollection);

                $tmp_hashes = [];
                for ($i = 0; $i < range(0, (1 << (count($layer_hashes) - 1)) - count($layer_hashes)); $i++) {
                    $tmp_hashes[] = $pad_piece_hash;
                }
                $layer_hashes = array_merge($tmp_hashes);
            }

            $this->root = self::rootHash($layer_hashes);
        }
    }

    /**
     * Compute the root hash of a Merkle tree with the given list of leaf hashes
     *
     * @param array $hashes
     * @return mixed
     */
    public static function rootHash(array $hashes)
    {
        assert(count($hashes) & (count($hashes) - 1) === 0);
        while (count($hashes) > 1) {
            foreach ($hashes as $l => $r) {
                $hashes[] = hash('sha256', $l.$r);
            }
        }

        return $hashes[0];
    }

    /**
     * Append data to the hash resource using hash_update()
     *
     * @return string
     */
    public function appendPadding(): string
    {
        hash_update($this->padHasher, $this->padLength);
        $pad_hash_tmp = hash_copy($this->padHasher);

        return hash_final($pad_hash_tmp);
    }

    /**
     * Return the final hash and discard the previous hash resource
     *
     * @return string
     */
    public function discardPadding(): string
    {
        return hash_final($this->padHasher);
    }
}
