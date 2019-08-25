# laravel-openedx

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]


Laravel connector for OpenEdx Learning Management System.

## Installation

Via Composer

``` bash
$ composer require ngunyimacharia/laravel-openedx
```

## Configuration

1. Run `php artisan vendor:publish` to push the configuration file for the package. This file will be saved as `openedx.php`
2. Configure the following required values in your `.env` file 

	MICROSITE_BASE=example.com
	MICROSITE_URL=http(s)://example.com
	LMS_BASE=https://courses.example.com
	CMS_BASE=https://studio.courses.example.com
	LMS_REGISTRATION_URL=https://courses.example.com/user_api/v1/account/registration/
	LMS_LOGIN_URL=https://courses.example.com/user_api/v1/account/login_session/
	LMS_RESET_PASSWORD_PAGE=https://courses.example.com/user_api/v1/account/password_reset/
	LMS_RESET_PASSWORD_API_URL=https://courses.example.com/user_api/v1/account/password_change/ (custom configured)
	REGISTER_EMAIL_FIELD=register_email
	REGISTER_PASSWORD_FIELD=register_password
	LOGIN_EMAIL_FIELD=login_email
	LOGIN_PASSWORD_FIELD=login_password
	EDX_DB_HOST=127.0.0.1
	EDX_DB_USERNAME=root
	EDX_DB_PASSWORD=password
	EDX_KEY=xxxxxxxxx (for api access)
	EDX_SECRET=xxxxxxxxx (for api access)

## Usage

Add openedx facade to file

```use ngunyimacharia\openedx\Facades\openedx;```

### Register user
```
Openedx::register([
'first_name'=>$first_name,
'last_name'=>$last_name,
'username'=>$username,
'email'=> $email,
'password'=> $hashed_password
]);
```
Response: Boolean (if operation is successful)

### Login user
```
Openedx::login(['username'  => $username', 'password'  => $password']);
```
Response: Cookies saved to ensure sign-in.

### Logout user
```
Openedx::logout()
```
Response: Boolean (if operation is successful

_NB: It is recommended to create an iframe to call the LMS logout url as opposed to using this method to logout of the LMS._

### Get all courses
```
Openedx::getCourses()
```
Response: Array of all courses currently in LMS

### Get course overview

```
Openedx::getOverview($courseId)
```
Response:  Course overview

### Check enrollment status
```
Openedx::checkEnrollmentStatus($courseId)
```
Response: Enrollment status of current authenticated user for the course specified. 

### Enroll into course
```
Openedx::enroll($courseId)
```
Response: Boolean (Based on success of operation)

### Enrollments
```
Openedx::enrollments()
```
Response: Array of all course enrollments LMS wide. 

### Get course progress
```
Openedx::getCourseProgress($courseId)
```
Response: String status of authenticated user in current course.

## Changelog

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing (Pending)

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email author email instead of using the issue tracker.

## Credits

- [Kelvin Macharia](https://github.com/ngunyimacharia)

## License

Please see the [license file](license.md) for more information.


[ico-version]: https://img.shields.io/packagist/v/ngunyimacharia/laravel-openedx.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/ngunyimacharia/laravel-openedx.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/ngunyimacharia/laravel-openedx/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/ngunyimacharia/laravel-openedx
[link-downloads]: https://packagist.org/packages/ngunyimacharia/laravel-openedx
[link-travis]: https://travis-ci.org/ngunyimacharia/laravel-openedx
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/ngunyimacharia
[link-contributors]: ../../contributors