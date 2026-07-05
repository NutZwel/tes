<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Playlist_model extends CI_Model {

    /**
     * Get all playlists for a user, with song count.
     *
     * @param int $userId
     * @return array
     */
    public function get_by_user($userId)
    {
        $this->db->select('
            playlists.*,
            (SELECT COUNT(*) FROM playlist_songs WHERE playlist_songs.playlist_id = playlists.id) AS song_count
        ');
        $this->db->where('playlists.user_id', $userId);
        $this->db->order_by('playlists.updated_at', 'DESC');
        return $this->db->get('playlists')->result();
    }

    /**
     * Get a single playlist with its songs.
     *
     * @param int $playlistId
     * @param int $userId      For ownership check
     * @return object|null
     */
    public function get_with_songs($playlistId, $userId)
    {
        $playlist = $this->db->where('id', $playlistId)
                             ->where('user_id', $userId)
                             ->get('playlists')
                             ->row();
        if (!$playlist) return null;

        $this->db->select('
            songs.id, songs.title, songs.artist, songs.duration_seconds,
            songs.cover_path, genres.name AS genre_name
        ');
        $this->db->from('playlist_songs');
        $this->db->join('songs', 'songs.id = playlist_songs.song_id');
        $this->db->join('genres', 'genres.id = songs.genre_id', 'left');
        $this->db->where('playlist_songs.playlist_id', $playlistId);
        $this->db->where('songs.is_active', 1);
        $this->db->order_by('playlist_songs.position', 'ASC');
        $playlist->songs = $this->db->get()->result();

        return $playlist;
    }

    /**
     * Create a new playlist.
     *
     * @param int   $userId
     * @param array $data    Keys: title, description, is_public
     * @return int           Inserted playlist ID
     */
    public function create($userId, array $data)
    {
        $insert = [
            'user_id'     => $userId,
            'name'        => $data['title'],   // maps to schema 'name' column
            'description' => $data['description'] ?? '',
            'is_public'   => !empty($data['is_public']) ? 1 : 0,
        ];
        $this->db->insert('playlists', $insert);
        return (int) $this->db->insert_id();
    }

    /**
     * Add a song to a playlist.
     *
     * @param int $playlistId
     * @param int $songId
     * @param int $position  Optional sort position
     * @return bool
     */
    public function add_song($playlistId, $songId, $position = 0)
    {
        $exists = $this->db->where('playlist_id', $playlistId)
                           ->where('song_id', $songId)
                           ->get('playlist_songs')
                           ->row();
        if ($exists) return true; // already added

        return $this->db->insert('playlist_songs', [
            'playlist_id' => $playlistId,
            'song_id'     => $songId,
            'position'    => $position,
        ]);
    }

    /**
     * Remove a song from a playlist.
     *
     * @param int $playlistId
     * @param int $songId
     * @return bool
     */
    public function remove_song($playlistId, $songId)
    {
        $this->db->where('playlist_id', $playlistId)
                 ->where('song_id', $songId)
                 ->delete('playlist_songs');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Count playlists for a user.
     *
     * @param int $userId
     * @return int
     */
    public function count_by_user($userId)
    {
        return $this->db->where('user_id', $userId)->count_all_results('playlists');
    }

    /**
     * Delete a playlist (user owns it).
     *
     * @param int $playlistId
     * @param int $userId
     * @return bool
     */
    public function delete($playlistId, $userId)
    {
        $this->db->where('id', $playlistId)->where('user_id', $userId)->delete('playlists');
        return $this->db->affected_rows() > 0;
    }

    /**
     * Search public playlists by name.
     */
    public function search_public($query)
    {
        $like = '%' . $this->db->escape_like_str($query) . '%';
        $this->db->select('id, name, description, cover_path');
        $this->db->from('playlists');
        $this->db->where('is_public', 1);
        $this->db->like('name', $query);
        $this->db->limit(10);
        return $this->db->get()->result();
    }
}
