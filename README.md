# WordPress Examity Client Plugin

## Prerequisites

Requires the PHP mcrypt extension and the LearnDash LMS plugin.

## Setup

1. Clone this repository to wp-content/plugins

2. Install composer.

```
wget https://getcomposer.org/installer
php installer
rm installer
```

3. Use composer to install dependencies.

```
php composer.phar install
```

4. Activate the plugin in WordPress.

5. Configure the plugin using the API connection and SSO details provided by Examity.

6. The plugin will now automatically register students, create courses and exams, and enroll students in classes. These actions are triggerd by users browsing learndash courses.

7. This plugin provides the following shortcode for an Examity SSO button: ```[examity-client-login]```
