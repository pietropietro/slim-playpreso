<?php

declare(strict_types=1);

namespace App\Repository;

final class LeagueRepository extends BaseRepository
{
    public function getForArea(string $area, int $level)
    {
        $this->getDb()->where('area', $area);

        switch ($level) {
			case 1:
			case 2:
			case 3:
                $this->getDb()->where('area_level',$level);
                $this->getDb()->where('country_level',1);
				break;
			case 4:
                $this->getDb()->where('country_level',2);
				break;
		}

        return $this->getDb()->get('leagues');
    }

    public function getUefa(){
		$this->getDb()->where('country','AAA');
		return $this->getDb()->get('leagues');
    }

    public function getForCountry(?string $country, int $level){
        if($country){
            $this->getDb()->where('country',$country);
        }
        $this->getDb()->where('country_level', $level, '<=');
        return $this->getDb()->get('leagues');
    }
}