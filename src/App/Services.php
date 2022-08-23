<?php

declare(strict_types=1);

use App\Service\User;
use App\Service\PPLeague;
use App\Service\PPTournamentType;
use App\Service\PPRound;
use App\Service\PPRoundMatch;
use App\Service\PPCup;
use App\Service\PPCupGroup;
use App\Service\UserParticipation;
use App\Service\League;
use App\Service\ExternalAPI;
use App\Service\Match;
use App\Service\Guess;
use App\Service\Score;
use Psr\Container\ContainerInterface;

$container['find_user_service'] = static fn (
    ContainerInterface $container
): User\Find => new User\Find(
    $container->get('user_repository'),
    $container->get('redis_service'),
);

$container['create_user_service'] = static fn (
    ContainerInterface $container
): User\Create => new User\Create(
    $container->get('user_repository'),
    $container->get('redis_service')
);

$container['update_user_service'] = static fn (
    ContainerInterface $container
): User\Update => new User\Update(
    $container->get('user_repository'),
    $container->get('redis_service')
);

$container['delete_user_service'] = static fn (
    ContainerInterface $container
): User\Delete => new User\Delete(
    $container->get('user_repository'),
    $container->get('redis_service')
);

$container['login_user_service'] = static fn (
    ContainerInterface $container
): User\Login => new User\Login(
    $container->get('user_repository'),
    $container->get('redis_service')
);

$container['guess_service'] = static fn (
    ContainerInterface $container
): GuessService => new GuessService(
    $container->get('guess_repository'),
    $container->get('redis_service')
);

//TODO REMOVE UNUSED SERVICES REPO
$container['ppleague_find_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Find => new  PPLeague\Find(
    $container->get('redis_service'),
    $container->get('ppleague_repository'),
    $container->get('pptournamenttype_find_service'),
);

$container['ppleague_update_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Update => new  PPLeague\Update(
    $container->get('redis_service'),
    $container->get('ppleague_repository'),
    $container->get('pptournamenttype_repository'),
    $container->get('ppround_repository'),
    $container->get('userparticipation_repository'),
    $container->get('user_repository'),
    $container->get('guess_repository')
);

$container['ppleague_update_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Update => new  PPLeague\Update(
    $container->get('redis_service'),
    $container->get('ppleague_repository'),
);


$container['pptournamenttype_find_service'] = static fn (
    ContainerInterface $container
):  PPTournamentType\Find => new  PPTournamentType\Find(
    $container->get('redis_service'),
    $container->get('pptournamenttype_repository'),
    $container->get('userparticipation_repository'),
    $container->get('user_points_service'),
    $container->get('league_find_service'),
);


$container['ppround_find_service'] = static fn (
    ContainerInterface $container
):  PPRound\Find => new  PPRound\Find(
    $container->get('redis_service'),
    $container->get('pproundmatch_find_service'),
    $container->get('ppround_repository'),
);

$container['pproundmatch_find_service'] = static fn (
    ContainerInterface $container
):  PPRoundMatch\Find => new  PPRoundMatch\Find(
    $container->get('redis_service'),
    $container->get('pproundmatch_repository'),
    $container->get('guess_repository'),
    $container->get('match_find_service')
);


$container['ppround_verify_service'] = static fn (
    ContainerInterface $container
):  PPRound\Verify => new  PPRound\Verify(
    $container->get('ppround_find_service'),
    $container->get('ppleague_verify_service'),
    $container->get('userparticipation_update_service'),
);


//TODO understand how to remove duplicates 
//different constructros in base services vs detailed?
$container['userparticipation_find_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Find => new  UserParticipation\Find(
    $container->get('redis_service'),
    $container->get('userparticipation_repository'),
    $container->get('pptournamenttype_repository'),
    $container->get('ppleague_repository'),
    $container->get('ppround_repository'),
    $container->get('guess_repository')
    // $container->get('user_repository')
);

$container['userparticipation_create_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Create => new  UserParticipation\Create(
    $container->get('userparticipation_repository'),
    $container->get('ppleague_repository'),
);

$container['userparticipation_update_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Update => new  UserParticipation\Update(
    $container->get('redis_service'),
    $container->get('userparticipation_repository'),
    $container->get('pptournamenttype_repository'),
    $container->get('ppleague_repository'),
    $container->get('ppround_repository'),
    $container->get('guess_repository')
);

$container['user_points_service'] = static fn (
    ContainerInterface $container
):  User\Points => new  User\Points(
    $container->get('user_repository'),
    $container->get('redis_service'),
);

$container['league_find_service'] = static fn (
    ContainerInterface $container
):  League\Find => new  League\Find(
    $container->get('redis_service'),
    $container->get('pptournamenttype_repository'),
    $container->get('league_repository'),
);


$container['ppcup_count_service'] = static fn (
    ContainerInterface $container
):  PPCup\Count => new  PPCup\Count(
    $container->get('redis_service'),
    $container->get('userparticipation_update_service'),
    $container->get('ppcupgroup_repository'),
);

$container['ppcup_find_service'] = static fn (
    ContainerInterface $container
):  PPCup\Find => new  PPCup\Find(
    $container->get('redis_service'),
    $container->get('userparticipation_find_service'),
    $container->get('ppcup_repository'),
    $container->get('ppcupgroup_repository'),
);


$container['ppcupgroup_service'] = static fn (
    ContainerInterface $container
):  PPCupGroup\Find => new  PPCupGroup\Find(
    $container->get('redis_service'),
    $container->get('ppcupgroup_repository'),
);

$container['external_api_service'] = static fn (
    ContainerInterface $container
):  ExternalAPI\Call => new  ExternalAPI\Call(
    $container->get('match_elaborate_service'),
);

$container['match_elaborate_service'] = static fn (
    ContainerInterface $container
):  Match\Elaborate => new  Match\Elaborate(
    $container->get('match_repository'),
    $container->get('team_repository'),
    $container->get('guess_verify_service'),
    $container->get('ppround_verify_service')
);

$container['guess_verify_service'] = static fn (
    ContainerInterface $container
):  Guess\Verify => new  Guess\Verify(
    $container->get('guess_repository'),
    $container->get('score_service'),
    $container->get('user_points_service')
);

$container['score_service'] = static fn (
    ContainerInterface $container
):  Score\Calculate => new  Score\Calculate();

$container['ppleague_verify_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Verify => new  PPLeague\Verify(
    $container->get('ppleague_repository'),
    $container->get('ppleague_find_service'),
    $container->get('ppround_create_service'),
    $container->get('userparticipation_update_service')
);

$container['match_picker_service'] = static fn (
    ContainerInterface $container
):  Match\Picker => new Match\Picker(
    $container->get('match_repository'),
    $container->get('league_find_service'),
);

$container['match_find_service'] = static fn (
    ContainerInterface $container
):  Match\Find => new Match\Find(
    $container->get('match_repository'),
    $container->get('team_repository'),
    $container->get('league_find_service'),
);

$container['ppround_create_service'] = static fn (
    ContainerInterface $container
):  PPRound\Create => new  PPRound\Create(
    $container->get('match_picker_service'),
    $container->get('ppround_repository'),
    $container->get('pproundmatch_create_service'),
);

$container['pproundmatch_create_service'] = static fn (
    ContainerInterface $container
):  PPRoundMatch\Create => new  PPRoundMatch\Create(
    $container->get('pproundmatch_repository'),
    $container->get('guess_create_service'),
);

$container['guess_create_service'] = static fn (
    ContainerInterface $container
):  Guess\Create => new  Guess\Create(
    $container->get('guess_repository'),
    $container->get('userparticipation_repository'),
);
