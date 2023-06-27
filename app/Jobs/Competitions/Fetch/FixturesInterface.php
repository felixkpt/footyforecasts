<?php

namespace App\Jobs\Competitions\Fetch;

/**
 * Interface FixturesInterface
 * This interface defines the contract for classes that fetch fixtures for competitions.
 */
interface FixturesInterface
{
    /**
     * Update the fixtures for the given competitions.
     *
     * @param array $competitions An array of competition objects
     * @return void
     */
    function update($competitions);

    /**
     * Handle fixtures for teams.
     *
     * @param array $all_res An array of all fixtures
     * @param array $teams An array of team objects
     * @param object|null $gameModel The game model object (optional)
     * @param object|null $competition The competition object (optional)
     * @return array The updated array of fixtures
     */
    function handleTeamFixtures($all_res, $teams, $gameModel = null, $competition = null);
}
