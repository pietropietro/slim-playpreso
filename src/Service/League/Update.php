<?php

declare(strict_types=1);

namespace App\Service\League;

use App\Repository\LeagueRepository;
use App\Service\BaseService;

final class Update extends BaseService
{
    public function __construct(
        protected LeagueRepository $leagueRepository,
    ) {}

    public function update(int $id, array $data){

        $this->leagueRepository->update($id, $data);

        //update all children leagues
        if($data['parent_id'] && $data['parent_id'] == $id){
            $children = $this->leagueRepository->getChildren($id, true);
            foreach($children as $child){
                $childData = array(
                    'level' => $data['level'],
                    'country' => $data['country']
                );
                $this->leagueRepository->update($child['id'], $data);
            }
        }
    }

}

