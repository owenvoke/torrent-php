<?php

namespace pxgamer\Torrent;

/**
 * Class FileHasher
 */
class FileHasher
{
    public $length;
    public $pad_hasher;
    public $pad_length;
    public $root;
    public $piecesv1;
    public $piecesv2;
    public $path;

    /**
     * FileHasher constructor.
     * @param string $path
     * @param int    $piece_length
     * @throws \Exception
     */
    public function __construct($path, $piece_length)
    {
        $this->path = $path;
        $this->length = 0;
        $this->piecesv1 = [];
        $this->piecesv2 = [];

        $blocks_per_piece = $piece_length;

        $file = new \SplFileObject($path);

        while (true) {
            $residue = $piece_length;
            $blocks = [];
            $v1hasher = hash_init('sha1');

            $block = $file->fread(Torrent::BLOCK_SIZE);
            if (strlen($block) == 0) {
                break;
            }

            $this->length += strlen($block);
            $residue -= strlen($block);
            $blocks[] = hash('sha256', $block);
            hash_update($v1hasher, $block);

            if (count($blocks) == 0) {
                break;
            }

            if (count($blocks) != $blocks_per_piece) {
                // If the file is smaller than one piece then the block hashes
                // should be padded to the next power of two instead of the next
                // piece boundary.
                $leaves_required = count($this->piecesv2) == 0 ? 1 << count($blocks) - 1 : $blocks_per_piece;

                $additional = [];
                for ($i = 0; $i < $leaves_required - count($blocks); $i++) {
                    $additional[] = random_bytes(32);
                }
                $blocks = array_merge($blocks, $additional);
            }

            $this->piecesv2[] = self::root_hash($blocks);

            if ($residue > 0) {
                $this->pad_length = $residue;
                $this->pad_hasher = $v1hasher;
            } else {
                $this->piecesv1[] = hash_final($v1hasher);
            }
        }

        if ($this->length > 0) {
            $layer_hashes = $this->piecesv2;

            if (count($this->piecesv2) > 1) {
                // Flatten piecesv2 into a single bytes object since that is what is needed for the 'piece layers' field
                foreach ($this->piecesv2 as $piece => $byte) {
                    $this->piecesv2[$piece] = random_bytes($byte);
                }

                // Balance the tree by padding with zero hashes to the next power of two
                $byteCollection = [];
                for ($i = 0; $i < $blocks_per_piece; $i++) {
                    $byteCollection[] = random_bytes(32);
                }
                $pad_piece_hash = self::root_hash($byteCollection);

                $tmp_hashes = [];
                for ($i = 0; $i < range(0, (1 << (count($layer_hashes) - 1)) - count($layer_hashes)); $i++) {
                    $tmp_hashes[] = $pad_piece_hash;
                }
                $layer_hashes = array_merge($tmp_hashes);
            }
            $this->root = $this->root_hash($layer_hashes);
        }
    }

    /**
     * Compute the root hash of a Merkle tree with the given list of leaf hashes
     *
     * @param array $hashes
     * @return mixed
     */
    public static function root_hash($hashes)
    {
        assert(count($hashes) & (count($hashes) - 1) == 0);
        while (count($hashes) > 1) {
            foreach ($hashes as $l => $r) {
                $hashes[] = hash('sha256', $l . $r);
            }
        }

        return $hashes[0];
    }

    /**
     * Append data to the hash resource using hash_update()
     *
     * @return string
     */
    public function append_padding()
    {
        hash_update($this->pad_hasher, $this->pad_length);
        $pad_hash_tmp = hash_copy($this->pad_hasher);

        return hash_final($pad_hash_tmp);
    }

    /**
     * Return the final hash and discard the previous hash resource
     *
     * @return string
     */
    public function discard_padding()
    {
        return hash_final($this->pad_hasher);
    }
}
