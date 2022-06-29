<?php

declare(strict_types=1);

use App\Service\User;
use App\Service\PPLeague;
use App\Service\PPLeagueType;
use App\Service\PPRound;
use App\Service\PPCup;
use App\Service\UserParticipation;
use App\Service\League;
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
$container['ppleague_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Find => new  PPLeague\Find(
    $container->get('redis_service'),
    $container->get('ppleague_repository'),
);

$container['ppleague_update_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Update => new  PPLeague\Update(
    $container->get('redis_service'),
    $container->get('ppleague_repository'),
    $container->get('ppleaguetype_repository'),
    $container->get('ppround_repository'),
    $container->get('user_participations_repository'),
    $container->get('user_repository'),
    $container->get('guess_repository')
);

$container['ppleague_update_service'] = static fn (
    ContainerInterface $container
):  PPLeague\Update => new  PPLeague\Update(
    $container->get('redis_service'),
    $container->get('ppleague_repository'),
);


$container['ppleaguetype_service'] = static fn (
    ContainerInterface $container
):  PPLeagueType\Find => new  PPLeagueType\Find(
    $container->get('redis_service'),
    $container->get('ppleaguetype_repository'),
    $container->get('user_participations_repository'),
    $container->get('user_points_service'),
    $container->get('leagues_service'),
);


$container['ppround_service'] = static fn (
    ContainerInterface $container
):  PPRound\Find => new  PPRound\Find(
    $container->get('redis_service'),
    $container->get('ppround_repository'),
    $container->get('ppround_match_repository'),
    $container->get('guess_repository'),
    $container->get('match_repository')
);



//TODO understand how to remove duplicates 
//different constructros in base services vs detailed?
$container['user_participation_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Find => new  UserParticipation\Find(
    $container->get('redis_service'),
    $container->get('user_participations_repository'),
    $container->get('ppleaguetype_repository'),
    $container->get('ppleague_repository'),
    $container->get('ppround_repository'),
    $container->get('guess_repository')
    // $container->get('user_repository')
);

$container['user_participation_create_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Create => new  UserParticipation\Create(
    $container->get('user_participations_repository'),
    $container->get('ppleague_repository'),
);

$container['user_participation_update_service'] = static fn (
    ContainerInterface $container
):  UserParticipation\Update => new  UserParticipation\Update(
    $container->get('redis_service'),
    $container->get('user_participations_repository'),
    $container->get('ppleaguetype_repository'),
    $container->get('ppleague_repository'),
    $container->get('ppround_repository'),
    $container->get('guess_repository')
);

$container['user_points_service'] = static fn (
    ContainerInterface $container
):  User\Points => new  User\Points(
    $container->get('user_repository'),
    $container->get('redis_service'),
    $container->get('ppleaguetype_repository'),
);

$container['leagues_service'] = static fn (
    ContainerInterface $container
):  League\Find => new  League\Find(
    $container->get('redis_service'),
    $container->get('ppleaguetype_repository'),
    $container->get('league_repository'),
);


$container['ppcup_count_service'] = static fn (
    ContainerInterface $container
):  PPCup\Count => new  PPCup\Count(
    $container->get('redis_service'),
    $container->get('user_participation_update_service'),
    $container->get('ppcup_repository'),
    $container->get('ppcupgroup_repository'),
);