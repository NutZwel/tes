<?php
defined('BASEPATH') OR exit('no direct script access allowed');

class Lyrics_model extends CI_Model {

    /**
     * Get lyrics for a song from local database.
     *
     * @param int $songId
     * @return object|null  Object with 'content' and 'format' properties
     */
    public function get_by_song($songId)
    {
        $this->db->select('content, format');
        $this->db->from('lyrics');
        $this->db->where('song_id', $songId);
        return $this->db->get()->row();
    }

    /**
     * Cache lyrics fetched from external API to local database.
     *
     * @param int    $songId
     * @param string $content
     * @param string $format  'plain' or 'lrc'
     * @return bool
     */
    public function cache($songId, $content, $format)
    {
        return $this->db->replace('lyrics', [
            'song_id' => $songId,
            'content' => $content,
            'format'  => $format,
        ]);
    }
}
