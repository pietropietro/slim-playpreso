<?php

declare(strict_types=1);

namespace App\Repository;

final class PPRoundRepository extends BaseRepository
{
    public function count(string $column, int $valueId) : int {
        $this->getDb()->where($column,$valueId);
        $sql = "SELECT COUNT(*) as round_count FROM ppRounds";
        $result = $this->getDb()->query($sql);
        return $result[0]['round_count'];
    }

    public function getForTournament($column, $valueId){
        $this->getDb()->where($column, $valueId);
        return $this->getDb()->get('ppRounds');
    }

    public function getOne(int $id){
        $this->getDb()->where('id', $id);
        return $this->getDb()->getOne('ppRounds');
    }
}
