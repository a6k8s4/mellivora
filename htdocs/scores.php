<?php

define('IN_FILE', true);
require('../include/general.inc.php');

head('Scoreboard');

echo '
<div class="row-fluid">
    <div class="span6">';

sectionHead('Scoreboard');

echo '
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Team name</th>
          <th>Points</th>
        </tr>
      </thead>
      <tbody>
     ';

$chal_stmt = $db->query('
    SELECT
    u.id AS user_id,
    u.team_name,
    SUM(c.points) AS score,
    SUM(s.added) AS tiebreaker
    FROM users AS u
    LEFT JOIN submissions AS s ON u.id = s.user_id AND s.correct = 1
    LEFT JOIN challenges AS c ON c.id = s.challenge
    GROUP BY u.id
    ORDER BY score DESC, tiebreaker ASC
');

$i = 1;
while($place = $chal_stmt->fetch(PDO::FETCH_ASSOC)) {

echo '
    <tr>
      <td>', number_format($i) , '</td>
      <td>';
        if ($_SESSION['id']) {

            echo '<a href="user?id=',htmlspecialchars($place['user_id']),'">',
                    ($place['user_id'] == $_SESSION['id'] ? '<span class="label label-info">'.htmlspecialchars($place['team_name']).'</span>' : htmlspecialchars($place['team_name'])),
                 '</a>';
        }
        else {
            echo htmlspecialchars($place['team_name']);
        }
        echo '
      </td>
      <td>' , number_format($place['score']), '</td>
    </tr>
';

    $i++;
}

echo '
      </tbody>
    </table>

    </div>  <!-- / span6 -->

    <div class="span6">
    ';

sectionHead('Challenges');

$cat_stmt = $db->query('SELECT * FROM categories ORDER BY title');
while($category = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {

    sectionSubHead($category['title']);

    $chal_stmt = $db->prepare('
        SELECT
        id,
        title,
        points
        FROM challenges
        WHERE category = :category
        ORDER BY points ASC
    ');

    echo '<ul>';
    $chal_stmt->execute(array(':category' => $category['id']));
    while($challenge = $chal_stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<li>',htmlspecialchars($challenge['title']),' (',number_format($challenge['points']),'pts)</li>';

        $pos_stmt = $db->prepare('
            SELECT
            u.team_name,
            s.user_id,
            s.pos
            FROM submissions AS s
            JOIN users AS u ON u.id = s.user_id
            WHERE s.pos >= 1 AND s.pos <= 3 AND s.correct = 1 AND s.challenge=:challenge
            ORDER BY s.pos ASC
        ');

        $pos_stmt->execute(array(':challenge' => $challenge['id']));
        while($pos = $pos_stmt->fetch(PDO::FETCH_ASSOC)) {
            echo getPositionMedal($pos['pos']), ' <a href="users?id=',htmlspecialchars($pos['user_id']),'">', htmlspecialchars($pos['team_name']), '</a>';
        }
    }
    echo '</ul>';
}

echo '
    </div> <!-- / span6 -->
</div> <!-- / row-fluid -->
';

foot();