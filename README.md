# WorDBless

[![CI](https://github.com/Automattic/wordbless/workflows/CI/badge.svg)](https://github.com/Automattic/wordbless/actions?query=workflow%3ACI)

WorDBless allows you to use WordPress core functions in your PHPUnit tests without having to set up a database and the whole WordPress environment

## Usage

### Require WorDBless

```bash
composer require --dev automattic/wordbless
```

### Make sure to copy db.php

Add this script to your `composer.json`:

```json
	"scripts": {
		"post-install-cmd": "WorDBless\\Composer\\InstallDropin::copy",
		"post-update-cmd": "WorDBless\\Composer\\InstallDropin::copy"
	},
```

Alternatively, you can manually copy `src/dbless-wpdb.php` to the `wordpress` folder created in your project under `wp-content/db.php`.

### Initialize it in your bootstrap file

In your PHP Unit bootstrap file add:

```php
require_once __DIR__ . '/../vendor/autoload.php'; // adjust the path as needed

\WorDBless\Load::load();
```

That's it! You can now use WordPress core functions in your tests!

### Writing tests

Extend the `BaseTestCase` in order to have all the setup and teardown in place.

```php
class My_Tests extends \WorDBless\BaseTestCase {

	public function test_add() {
		add_option( 'test', 123 );
		$this->assertEquals( 123, get_option( 'test' ) );
	}

}
```
Note WorDBless uses `@before` and `@after` annotations rather than overriding PHPUnit's `setUp` and `tearDown` methods.

If you choose not to extend this base class, no problem. Just remember that WorDBless won't be set up or torn down for you. Check BaseTestCase::set_up_wordbless() and BaseTestCase::teardown_wordbless() to see how to do it for yourself.

## What will work and what will not work?

Disclaimer: This is still experimental, so all testing is very welcome.

Basically any WordPress core function will work. Things like `wp_parse_args`, `add_query_arg`, etc.

Hooks and filters will work.

Most of the things that uses the database WILL NOT work, unless you believe in magic.

### What magic WorDBless adds?

#### Options

Manipulating options will work. `get_option`, `update_option`, `add_option` and `delete_option` should work fine!

#### Posts and Post meta

Manipulating (creating, updating, deleting) posts and attachments will work. Fetching a single post will also work. Here is a non-exaustive list of functions supported:

* `wp_insert_post`
* `wp_update_post`
* `wp_delete_post`
* `wp_trash_post`
* `wp_untrash_post`
* `get_post`
* `get_post_meta`
* `add_post_meta`
* `update_post_meta`
* `delete_post_meta`
* `get_metadata_by_mid`
* `update_metadata_by_mid`
* `delete_metadata_by_mid`
* `wp_insert_attachment`
* `wp_get_attachment_image`
* and almost anything related to the manipulation of one attachment

Note: Fetching posts using `WP_Query` will not work (yet)! To fetch a post, use `get_post( $id )`.

#### Users and capabilities

You can create, edit and delete users.

Here is a non-exaustive list of functions supported:

* `wp_insert_user`
* `wp_update_user`
* `wp_delete_user`
* `get_userdata`
* `new WP_User( $id )` to fetch a user
* `user_can`
* `current_user_can`
* `set_current_user`
* `get_current_user_id`
* `wp_get_current_user`
* `get_user_meta`
* `update_user_meta`
* `add_user_meta`
* `delete_user_meta`

Posts can be assigned to users and proper capabilities will be correctly checked. When deleting a user, reassigning posts to other user will also work.

Note: Fetching users using `WP_Users_Query` will not work! To fetch a user, use `get_userdata()`, `get_user_by` or `WP_User` class.

### Populating default options

By default, only `siteurl` and `home` options are populated with `http://example.org`.

If you want, you can add more options to be loaded by default. 

Just declare a `dbless_default_options()` function in your bootstrap and make it return an array where the keys are option names and values, options values.

## Examples

Here's a simple example, using only few WordPress functions:

[Jetpack Admin UI package](https://github.com/Automattic/jetpack/blob/master/projects/packages/admin-ui/tests/php)

And here a more complex example, using WorDBless to test REST endpoints, create users and play with hooks:

[Jetpack Backup package](https://github.com/Automattic/jetpack/blob/master/projects/packages/backup/tests/php)
## Running our CI locally

First [install phive](https://phar.io/#Install) globally on your computer.

Then issue the following single command.

```bash
composer run-script ci
```
