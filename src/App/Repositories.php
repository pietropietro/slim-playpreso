<?php

declare(strict_types=1);

use App\Repository\MatchRepository;
use App\Repository\PPLeagueRepository;
use App\Repository\PPTournamentTypeRepository;
use App\Repository\PPRoundRepository;
use App\Repository\PPRoundMatchRepository;
use App\Repository\PPCupRepository;
use App\Repository\PPCupGroupRepository;
use App\Repository\GuessRepository;
use App\Repository\UserRepository;
use App\Repository\UserParticipationRepository;
use App\Repository\LeagueRepository;
use App\Repository\TeamRepository;

use Psr\Container\ContainerInterface;

$container['user_repository'] = static fn (ContainerInterface $container): UserRepository => new UserRepository($container->get('db'));

$container['userparticipation_repository'] = static fn (ContainerInterface $container): UserParticipationRepository => new UserParticipationRepository($container->get('db'));

$container['league_repository'] = static fn (ContainerInterface $container): LeagueRepository => new LeagueRepository($container->get('db'));

$container['ppleague_repository'] = static fn (ContainerInterface $container): PPLeagueRepository => new PPLeagueRepository($container->get('db'));

$container['pptournamenttype_repository'] = static fn (ContainerInterface $container): PPTournamentTypeRepository => new PPTournamentTypeRepository($container->get('db'));

$container['guess_repository'] = static fn (ContainerInterface $container): GuessRepository => new GuessRepository($container->get('db'));

$container['match_repository'] = static fn (ContainerInterface $container): MatchRepository => new MatchRepository($container->get('db'));

$container['ppround_repository'] = static fn (ContainerInterface $container): PPRoundRepository => new PPRoundRepository($container->get('db'));

$container['pproundmatch_repository'] = static fn (ContainerInterface $container): PPRoundMatchRepository => new PPRoundMatchRepository($container->get('db'));

$container['ppcup_repository'] = static fn (ContainerInterface $container): PPCupRepository => new PPCupRepository($container->get('db'));

$container['ppcupgroup_repository'] = static fn (ContainerInterface $container): PPCupGroupRepository => new PPCupGroupRepository($container->get('db'));

$container['team_repository'] = static fn (ContainerInterface $container): TeamRepository => new TeamRepository($container->get('db'));
