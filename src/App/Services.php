<?php

declare(strict_types=1);

use App\Service\User;
use App\Service\PPLeague;
use App\Service\UserParticipation;
use Psr\Container\ContainerInterface;

$container['find_user_service'] = static fn (
    ContainerInterface $container
): User\Find => new User\Find(
    $container->get('user_repository'),
    $container->get('redis_service'),
    $container->get('user_participations_repository'),
    $container->get('guess_repository'),
    $container->get('match_repository'),
    $container->get('ppleaguetype_repository'),
    $container->get('ppleague_repository'),
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
    $container->get('ppleaguetype_repository'),
    $container->get('ppround_repository'),
    $container->get('user_participations_repository'),
    $container->get('user_repository'),
    $container->get('guess_repository')
);



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


