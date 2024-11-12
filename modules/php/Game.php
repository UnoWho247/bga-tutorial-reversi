<?php
/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * LwbTutorialReversi implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * Game.php
 *
 * This is the main file for your game logic.
 *
 * In this PHP file, you are going to defines the rules of the game.
 */
declare(strict_types=1);

namespace Bga\Games\LwbTutorialReversi;

require_once(APP_GAMEMODULE_PATH . "module/table/table.game.php");

class Game extends \Table
{
    const HTML_WHITE = "FFFFFF";
    const HTML_BLACK = "000000";

    private static array $CARD_TYPES;

    /**
     * Your global variables labels:
     *
     * Here, you can assign labels to global variables you are using for this game. You can use any number of global
     * variables with IDs between 10 and 99. If your game has options (variants), you also have to associate here a
     * label to the corresponding ID in `gameoptions.inc.php`.
     *
     * NOTE: afterward, you can get/set the global variables with `getGameStateValue`, `setGameStateInitialValue` or
     * `setGameStateValue` functions.
     */
    public function __construct()
    {
        parent::__construct();

        $this->initGameStateLabels([
            "my_first_global_variable" => 10,
            "my_second_global_variable" => 11,
            "my_first_game_variant" => 100,
            "my_second_game_variant" => 101,
        ]);        

        self::$CARD_TYPES = [
            1 => [
                "card_name" => clienttranslate('Troll'), // ...
            ],
            2 => [
                "card_name" => clienttranslate('Goblin'), // ...
            ],
            // ...
        ];
    }

    /**
     * Player action, example content.
     *
     * In this scenario, each time a player plays a card, this method will be called. This method is called directly
     * by the action trigger on the front side with `bgaPerformAction`.
     *
     * @throws BgaUserException
     */
    public function actPlayCard(int $card_id): void
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // check input values
        $args = $this->argPlayerTurn();
        $playableCardsIds = $args['playableCardsIds'];
        if (!in_array($card_id, $playableCardsIds)) {
            throw new \BgaUserException('Invalid card choice');
        }

        // Add your game logic to play a card here.
        $card_name = self::$CARD_TYPES[$card_id]['card_name'];

        // Notify all players about the card played.
        $this->notifyAllPlayers("cardPlayed", clienttranslate('${player_name} plays ${card_name}'), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(),
            "card_name" => $card_name,
            "card_id" => $card_id,
            "i18n" => ['card_name'],
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState("playCard");
    }

    public function actPass(): void
    {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // Notify all players about the choice to pass.
        $this->notifyAllPlayers("cardPlayed", clienttranslate('${player_name} passes'), [
            "player_id" => $player_id,
            "player_name" => $this->getActivePlayerName(),
        ]);

        // at the end of the action, move to the next state
        $this->gamestate->nextState("pass");
    }

    /**
     * Game state arguments, example content.
     *
     * This method returns some additional information that is very specific to the `playerTurn` game state.
     *
     * @return array
     * @see ./states.inc.php
     */
    public function argPlayerTurn(): array
    {
        // Get some values from the current game situation from the database.
        return [
            'possibleMoves' => $this->getPossibleMoves( intval($this->getActivePlayerId()) )
        ];
    }

    /**
     * Compute and return the current game progression.
     *
     * The number returned must be an integer between 0 and 100.
     *
     * This method is called each time we are in a game state with the "updateGameProgression" property set to true.
     *
     * @return int
     * @see ./states.inc.php
     */
    public function getGameProgression()
    {
        // TODO: compute and return the game progression

        return 0;
    }

    /**
     * Game state action, example content.
     *
     * The action method of state `nextPlayer` is called everytime the current game state is set to `nextPlayer`.
     */
    public function stNextPlayer(): void {
        // Retrieve the active player ID.
        $player_id = (int)$this->getActivePlayerId();

        // Give some extra time to the active player when he completed an action
        $this->giveExtraTime($player_id);
        
        $this->activeNextPlayer();

        // Go to another gamestate
        // Here, we would detect if the game is over, and in this case use "endGame" transition instead 
        $this->gamestate->nextState("nextPlayer");
    }

    // public function stNextPlayer(): void {
    //     // Active next player
    //     $player_id = intval($this->activeNextPlayer());

    //     // Check if both player has at least 1 discs, and if there are free squares to play
    //     $player_to_discs = $this->getCollectionFromDb( "SELECT board_player, COUNT( board_x )
    //                                                     FROM board
    //                                                     GROUP BY board_player", true );

    //     if(!isset($player_to_discs[null])) {
    //         // Index 0 has not been set => there's no more free place on the board !
    //         // => end of the game
    //         $this->gamestate->nextState('endGame');
    //         return;
    //     } else if (!isset($player_to_discs[$player_id])) {
    //         // Active player has no more disc on the board => he looses immediately
    //         $this->gamestate->nextState('endGame');
    //         return;
    //     }
        
    //     // Can this player play?
    //     $possibleMoves = $this->getPossibleMoves($player_id);
    //     if (count($possibleMoves) == 0) {
    //         // This player can't play
    //         // Can his opponent play ?
    //         $opponent_id = (int)$this->getUniqueValueFromDb( "SELECT player_id FROM player WHERE player_id!='$player_id'");
    //         if(count($this->getPossibleMoves($opponent_id)) == 0) {
    //             // Nobody can move => end of the game
    //             $this->gamestate->nextState('endGame');
    //         } else {            
    //             // => pass his turn
    //             $this->gamestate->nextState('cantPlay');
    //         }
    //     } else {
    //         // This player can play. Give him some extra time
    //         $this->giveExtraTime( $player_id );
    //         $this->gamestate->nextState( 'nextTurn' );
    //     }
    // }

    /**
     * Migrate database.
     *
     * You don't have to care about this until your game has been published on BGA. Once your game is on BGA, this
     * method is called everytime the system detects a game running with your old database scheme. In this case, if you
     * change your database scheme, you just have to apply the needed changes in order to update the game database and
     * allow the game to continue to run with your new version.
     *
     * @param int $from_version
     * @return void
     */
    public function upgradeTableDb($from_version)
    {
//       if ($from_version <= 1404301345)
//       {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            $this->applyDbUpgradeToAllDB( $sql );
//       }
//
//       if ($from_version <= 1405061421)
//       {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            $this->applyDbUpgradeToAllDB( $sql );
//       }
    }

    /*
     * Gather all information about current game situation (visible by the current player).
     *
     * The method is called each time the game interface is displayed to a player, i.e.:
     *
     * - when the game starts
     * - when a player refreshes the game page (F5)
     */
    protected function getAllDatas()
    {
        $result = [];

        // WARNING: We must only return information visible by the current player.
        $current_player_id = (int) $this->getCurrentPlayerId();

        // Get information about players.
        // NOTE: you can retrieve some extra field you added for "player" table in `dbmodel.sql` if you need it.
        $sql = "SELECT player_id id, player_score score, player_color color FROM player ";
        $result["players"] = $this->getCollectionFromDb($sql);
        // $result["players"] = $this->getCollectionFromDb(
        //     "SELECT `player_id` `id`, `player_score` `score` FROM `player`"
        // );

        // TODO: Gather all information about current game situation (visible by player $current_player_id).
        // Get reversi board token
        $result['board'] = self::getObjectListFromDB( "SELECT board_x x, board_y y, board_player player
            FROM board
            WHERE board_player IS NOT NULL" );

        return $result;
    }

    /**
     * Returns the game name.
     *
     * IMPORTANT: Please do not modify.
     */
    protected function getGameName()
    {
        return "lwbtutorialreversi";
    }

    /**
     * This method is called only once, when a new game is launched. In this method, you must setup the game
     *  according to the game rules, so that the game is ready to be played.
     */
    protected function setupNewGame($players, $options = [])
    {
        // Set the colors of the players with HTML color code. The default below is red/green/blue/orange/brown. The
        // number of colors defined here must correspond to the maximum number of players allowed for the gams.
        $gameinfos = $this->getGameinfos();
        $default_colors = array( self::HTML_BLACK, self::HTML_WHITE );
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $query_values = [];

        foreach ($players as $player_id => $player) {
            // Now you can access both $player_id and $player array
            $color = array_shift ( $default_colors );
            $query_values[] = vsprintf("('%s', '%s', '%s', '%s', '%s')", [
                $player_id,
                $color,
                $player["player_canal"],
                addslashes($player["player_name"]),
                addslashes($player["player_avatar"]),
            ]);

            if ($color == self::HTML_BLACK)
                $blackplayer_id = $player_id;
            else
                $whiteplayer_id = $player_id;
        }

        $sql .= implode(",", $query_values);
        $this->DbQuery( $sql );
        $this->reloadPlayersBasicInfos();

        // Init global values with their initial values.

        // TODO: Setup the initial game situation here.
        // Init the board
        $sql = "INSERT INTO board (board_x,board_y,board_player) VALUES ";
        $sql_values = array();

        for( $x=1; $x<=8; $x++ ) {
            for( $y=1; $y<=8; $y++ ) {
                $token_value = "NULL";
                if( ($x==4 && $y==4) || ($x==5 && $y==5) )  // Initial positions of white player
                    $token_value = "'$whiteplayer_id'";
                else if( ($x==4 && $y==5) || ($x==5 && $y==4) )  // Initial positions of black player
                    $token_value = "'$blackplayer_id'";
                                
                $sql_values[] = "('$x','$y',$token_value)";
            }
        }
        $sql .= implode( ',', $sql_values );
        $this->DbQuery( $sql );
        // Activate first player once everything has been initialized and ready.
        $this->activeNextPlayer();
    }

    /**
     * This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
     * You can do whatever you want in order to make sure the turn of this player ends appropriately
     * (ex: pass).
     *
     * Important: your zombie code will be called when the player leaves the game. This action is triggered
     * from the main site and propagated to the gameserver from a server, not from a browser.
     * As a consequence, there is no current player associated to this action. In your zombieTurn function,
     * you must _never_ use `getCurrentPlayerId()` or `getCurrentPlayerName()`, otherwise it will fail with a
     * "Not logged" error message.
     *
     * @param array{ type: string, name: string } $state
     * @param int $active_player
     * @return void
     * @throws feException if the zombie mode is not supported at this game state.
     */
    protected function zombieTurn(array $state, int $active_player): void
    {
        $state_name = $state["name"];

        if ($state["type"] === "activeplayer") {
            switch ($state_name) {
                default:
                {
                    $this->gamestate->nextState("zombiePass");
                    break;
                }
            }

            return;
        }

        // Make sure player is in a non-blocking status for role turn.
        if ($state["type"] === "multipleactiveplayer") {
            $this->gamestate->setPlayerNonMultiactive($active_player, '');
            return;
        }

        throw new \feException("Zombie mode not supported at this game state: \"{$state_name}\".");
    }

    // Utility functions

    // Get the complete board with a double associative array
    function getBoard()
    {
        $sql = "SELECT board_x x, board_y y, board_player player FROM board";
        return self::getDoubleKeyCollectionFromDB($sql, true);
    }

    function getFlippedDiscs($x, $y, $player_id, $board) {
        $flippedDiscs = array();

        // We can only work with empty spaces, otherwise a disc is already present in that space
        if ($board[$x][$y] === null) {
            $vectors = array (
                array(0, -1),   // N
                array(0,  1),   // S 
                array(1, 0),    // E
                array(-1, 0),   // W
                array(-1, -1),  // NW
                array(1, -1),   // NE
                array(-1, 1),   // SW
                array(1, 1)     // SE
            );

            foreach ($vectors as $vector) {
                $adjacent_x = $x;
                $adjacent_y = $y;
                $continue = true;
                $canFlip = array();

                while ($continue) {
                    $adjacent_x += $vector[0];
                    $adjacent_y += $vector[1];

                    if ($adjacent_x < 1 || $adjacent_x > 8 || $adjacent_y < 1 || $adjacent_y > 8) {
                        // Adjacent Space is off the board, stop checking this vector
                        $continue = false;
                    } else if (is_null($board[$adjacent_x][$adjacent_y])) {
                        // Empty space, stop checking this vector
                        $continue = false;
                    } else if ($board[$adjacent_x][$adjacent_y] != $player_id) {
                        // This is an opponent's disc, which can be flipped
                        $canFlip[] = array( 'x' => $adjacent_x, 'y' => $adjacent_y);
                    } else if ($board[$adjacent_x][$adjacent_y] == $player_id) {
                        // We've found another of our discs
                        if (count($canFlip) > 0) {
                            // There are opponent discs to flip between our 2 discs
                            $flippedDiscs = array_merge($flippedDiscs, $canFlip);
                        } 
                        // else no opponent discs to flip
                        $continue = false;
                    }
                }
            } 
        }

        return $flippedDiscs;
    }

    function getPossibleMoves($player_id) {
        $board = self::getBoard();
        $result = array();
        for ($x = 1; $x < 8; $x++) {
            for ($y = 1; $y < 8; $y++) {
                $flippedDiscs = self::getFlippedDiscs($x, $y, $player_id, $board);

                if (count($flippedDiscs) == 0) {
                    // Not a possible move
                } else {
                    // Set the 2nd dimension array first time
                    if (!isset($result[$x]))
                        $result[$x] = array();
                    $result[$x][$y] = true;
                }
            }
        }
        return $result;
    }

    // function actPlayDisc( int $x, int $y ) {
    //     $player_id = intval($this->getActivePlayerId()); 
        
    //     // Now, check if this is a possible move
    //     $board = $this->getBoard();
    //     $flippedDiscs = $this->getFlippedDiscs( $x, $y, $player_id, $board );
        
    //     if( count( $flippedDiscs ) > 0 ) {
    //         // This move is possible!
    //         // Let's place a disc at x,y and return all "$returned" discs to the active player
            
    //         $sql = "UPDATE board SET board_player='$player_id'
    //                 WHERE ( board_x, board_y) IN ( ";
            
    //         foreach( $flippedDiscs as $flipped )
    //         {
    //             $sql .= "('".$flipped['x']."','".$flipped['y']."'),";
    //         }
    //         $sql .= "('$x','$y') ) ";
                       
    //         $this->DbQuery( $sql );

    //         // Update scores according to the number of disc on board
    //         $sql = "UPDATE player
    //                 SET player_score = (
    //                 SELECT COUNT( board_x ) FROM board WHERE board_player=player_id
    //                 )";
    //         $this->DbQuery( $sql );
            
    //         // Statistics
    //         $this->incStat( count( $flippedDiscs ), "turnedOver", $player_id );
    //         if( ($x==1 && $y==1) || ($x==8 && $y==1) || ($x==1 && $y==8) || ($x==8 && $y==8) )
    //             $this->incStat( 1, 'discPlayedInCorner', $player_id );
    //         else if( $x==1 || $x==8 || $y==1 || $y==8 )
    //             $this->incStat( 1, 'discPlayedOnBorder', $player_id );
    //         else if( $x>=3 && $x<=6 && $y>=3 && $y<=6 )
    //             $this->incStat( 1, 'discPlayedInCenter', $player_id );

    //         // Notify
    //         $this->notifyAllPlayers( "playDisc", clienttranslate( '${player_name} plays a disc and turns over ${returned_nbr} disc(s)' ), array(
    //             'player_id' => $player_id,
    //             'player_name' => $this->getActivePlayerName(),
    //             'returned_nbr' => count( $flippedDiscs ),
    //             'x' => $x,
    //             'y' => $y
    //         ) );

    //         $this->notifyAllPlayers( "discsFlipped", '', array(
    //             'player_id' => $player_id,
    //             'turnedOver' => $flippedDiscs
    //         ) );
            
    //         $newScores = $this->getCollectionFromDb( "SELECT player_id, player_score FROM player", true );
    //         $this->notifyAllPlayers( "newScores", "", array(
    //             "scores" => $newScores
    //         ) );

    //         // Then, go to the next state
    //         $this->gamestate->nextState( 'playDisc' );
    //     } else
    //         throw new \BgaSystemException( "Impossible move" );
    // }
}
