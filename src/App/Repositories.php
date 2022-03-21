<?php

declare(strict_types=1);

use App\Repository\MatchRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\PPLeagueTypeRepository;
use App\Repository\GuessRepository;
use App\Repository\UserRepository;
use App\Repository\UserParticipationRepository;

use Psr\Container\ContainerInterface;

$container['user_repository'] = static fn (ContainerInterface $container): UserRepository => new UserRepository($container->get('db'));

$container['user_participations_repository'] = static fn (ContainerInterface $container): UserParticipationRepository => new UserParticipationRepository($container->get('db'));

$container['pp_league_repository'] = static fn (ContainerInterface $container): PPLeagueRepository => new PPLeagueRepository($container->get('db'));

$container['pp_league_type_repository'] = static fn (ContainerInterface $container): PPLeagueTypeRepository => new PPLeagueTypeRepository($container->get('db'));

$container['guess_repository'] = static fn (ContainerInterface $container): GuessRepository => new GuessRepository($container->get('db'));

$container['match_repository'] = static fn (ContainerInterface $container): MatchRepository => new MatchRepository($container->get('db'));

