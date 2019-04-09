Clone from repo 

git clone https://github.com/vishu099/lumen-backend-test.git

Move to folder directory
	cd team-management-test

Install composer
	
	composer install

Create .env File

	cp .env.example .env

Generate new Key
	php artisan key:generate

Set Database credentials according to local setup

Run migration

	php artisan migrate

Run seeds File

	php artisan seed

You can get admin credentials from database/seeds/UserTableSeeder

After login set the role of current user then you can access whole system