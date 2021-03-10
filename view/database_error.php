<?php
include './view/shared/header.php';
include './view/shared/nav.php';
?>
<main>
    <h2>Database Error</h2>
    <p class="first_paragraph">There was an error connecting to the database.</p>
    <p>The database must be installed as described in the appendix.</p>
    <p>MySQL must be running as described in chapter 1.</p>
    <p class="last_paragraph">Error message: <?= $error_message; ?></p>
</main>
</body>

</html>