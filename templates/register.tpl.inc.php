<?php

$csrf_token = $this->get_csrf_token();

echo <<<HTML
<form method="post" action="/register_account">
  <fieldset>
    <input type="hidden" name="do" value="yes"/>
    <input type="hidden" name="token" value="{$csrf_token}"/>
    <input type="hidden" id="password" name="password" value=""/>
    <input type="hidden" id="password2" name="password2" value=""/>
    <legend>Login Details</legend>
    <p>
      <label for="username">Username</label>
      <input type="text" id="username" name="username" value="{$args['username']}"/>
    </p>
    <p>
      <label for="password">Password</label>
      <input type="password" id="password" name="password"/>
    </p>
    <p>
      <label for="password2">Password verification</label>
      <input type="password" id="password2" name="password2"/>
    </p>
  </fieldset>
  <fieldset>
    <legend>User Details</legend>
    <p>
      <label for="name">Name</label>
      <input type="text" id="name" name="name" value="{$args['name']}"/>
    </p>
    <p>
      <label for="email">E-mail</label>
      <input type="text" id="email" name="email" value="{$args['email']}"/>
    </p>
  </fieldset>
  <div>
    <label>&nbsp;</label>
    <input type="submit" value="Submit" />
  </div>
</form>
HTML;
