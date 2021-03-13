<?php
include './view/shared/header.php';
include './view/shared/nav.php';
?>
<main>
    <h2>Register</h2>

    <form action="." method="post" id="registration_form" class="aligned">

        <label for="username">Username:</label>
        <input type="text" class="text" name="username" value="<?= $username; ?>" id="username">
        <?= $fields->getField('username')->getHTML(); ?>
        <br>

        <label for="password">Password:</label>
        <input type="password" class="text" name="password" value="<?= $password; ?>" id="password">
        <?= $fields->getField('password')->getHTML(); ?>
        <br>

        <label>&nbsp;</label>
        <input type="submit" name="action" value="Register">
    </form>
</main>
</body>

</html>