<?php

declare(strict_types=1);

namespace App\Repository;

final class PPDexRepository extends BaseRepository
{  
    public function getUserSchemaPPLeagues(int $userId): array
    {
        // Subquery for leagues
        $subQueryLeagues = "
            SELECT 
                up.*,
                pl.started_at AS up_started_at,
                pl.finished_at AS up_finished_at,
                ROW_NUMBER() OVER (PARTITION BY up.ppTournamentType_id ORDER BY up.position ASC, up.tot_points DESC) AS rn
            FROM 
                userParticipations up
            JOIN 
                ppLeagues pl ON up.ppLeague_id = pl.id
            WHERE 
                up.user_id = $userId
                AND up.ppLeague_id IS NOT NULL
                AND (pl.started_at IS NOT NULL OR pl.finished_at IS NOT NULL)
        ";

        // Main query
        $this->db->join("($subQueryLeagues) up_leagues", "pptt.id = up_leagues.ppTournamentType_id AND up_leagues.rn = 1", "LEFT");

        // Add where condition and order by clauses
        $this->db->where('pptt.cup_format', null, 'IS');
        $this->db->where('pptt.name', 'MOTD', '!=');
        $this->db->orderBy('pptt.name', 'ASC');
        $this->db->orderBy('pptt.level', 'ASC');

        // Define the columns to retrieve
        $columns = [
            'pptt.id AS pptt_id',
            'pptt.name AS pptt_name',
            'pptt.level AS pptt_level',
            'pptt.emoji AS pptt_emoji',
            'up_leagues.user_id AS up_user_id',
            'up_leagues.id AS up_id',
            'up_leagues.ppLeague_id AS up_ppLeague_id',
            'up_leagues.updated_at AS up_updated_at',
            'up_leagues.tot_points AS up_tot_points',
            'up_leagues.position AS up_position',
            'up_leagues.up_started_at',
            'up_leagues.up_finished_at'
        ];

        // Execute the query
        return $this->db->get('ppTournamentTypes pptt', null, $columns);
    }




    public function getUserSchemaPPCups(int $userId): array
    {
        // Subquery for cups
        $subQueryCups = "
            SELECT 
                up.*,
                ppcg.level AS up_cup_level,
                up.ppCupGroup_id AS up_ppCupGroup_id,
                pl.started_at AS up_started_at,
                pl.finished_at AS up_finished_at,
                ROW_NUMBER() OVER (PARTITION BY up.ppTournamentType_id ORDER BY ppcg.level DESC, up.position ASC) AS rn
            FROM 
                userParticipations up
            JOIN 
                ppCupGroups ppcg ON up.ppCupGroup_id = ppcg.id
            JOIN 
                ppCups pl ON up.ppCup_id = pl.id
            WHERE 
                up.user_id = $userId
                AND up.ppCup_id IS NOT NULL
        ";

        // Main query
        $this->db->join("($subQueryCups) up_cups", "pptt.id = up_cups.ppTournamentType_id AND up_cups.rn = 1", "LEFT");

        // Add where condition and order by clauses
        $this->db->where('pptt.cup_format', null, 'IS NOT');
        $this->db->where('pptt.name', 'MOTD', '!=');
        $this->db->orderBy('pptt.name', 'ASC');

        // Define the columns to retrieve
        $columns = [
            'pptt.id AS pptt_id',
            'pptt.name AS pptt_name',
            'pptt.level AS pptt_level',
            'pptt.emoji AS pptt_emoji',
            'pptt.cup_format AS pptt_cup_format',
            'up_cups.user_id AS up_user_id',
            'up_cups.id AS up_id',
            'up_cups.ppCup_id AS up_ppCup_id',
            'up_cups.updated_at AS up_updated_at',
            'up_cups.tot_points AS up_tot_points',
            'up_cups.position AS up_position',
            'up_cups.up_started_at AS up_started_at',
            'up_cups.up_finished_at AS up_finished_at',
            'up_cups.up_cup_level AS up_cup_level',
            'up_cups.up_ppCupGroup_id AS up_ppCupGroup_id'
        ];

        // Execute the query
        return $this->db->get('ppTournamentTypes pptt', null, $columns);
    }

}