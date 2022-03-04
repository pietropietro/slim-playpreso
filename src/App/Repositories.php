<?php

declare(strict_types=1);

use App\Repository\MatchRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\UserParticipationsRepository;
use App\Repository\GuessRepository;
use App\Repository\UserRepository;
use App\Repository\UserPlacementsRepository;

use Psr\Container\ContainerInterface;

$container['user_repository'] = static fn (ContainerInterface $container): UserRepository => new UserRepository($container->get('db'));

$container['user_participations_repository'] = static fn (ContainerInterface $container): UserParticipationsRepository => new UserParticipationsRepository($container->get('db'));

$container['pp_league_repository'] = static fn (ContainerInterface $container): PPLeagueRepository => new PPLeagueRepository($container->get('db'));

$container['guess_repository'] = static fn (ContainerInterface $container): GuessRepository => new GuessRepository($container->get('db'));

$container['match_repository'] = static fn (ContainerInterface $container): MatchRepository => new MatchRepository($container->get('db'));

//TODO merge w/ user_participations
$container['user_placements_repository'] = static fn (ContainerInterface $container): UserPlacementsRepository => new UserPlacementsRepository($container->get('db'));
