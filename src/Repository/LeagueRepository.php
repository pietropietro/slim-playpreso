<?php

declare(strict_types=1);

namespace App\Repository;

final class LeagueRepository extends BaseRepository
{
    public function getOne(int $id){
        $this->db->where('id', $id);
        return $this->db->getOne('leagues');
    }

    public function getForArea(string $area, int $level)
    {
        $this->db->where('area', $area);

        switch ($level) {
			case 1:
			case 2:
			case 3:
                $this->db->where('area_level',$level);
                $this->db->where('country_level',1);
				break;
			case 4:
                $this->db->where('country_level',2);
				break;
		}

        return $this->db->get('leagues');
    }

    public function getUefa(){
		$this->db->where('country','AAA');
		return $this->db->get('leagues');
    }

    public function getForCountry(?string $country, int $level){
        if($country){
            $this->db->where('country',$country);
        }
        $this->db->where('country_level', $level, '<=');
        return $this->db->get('leagues');
    }

}