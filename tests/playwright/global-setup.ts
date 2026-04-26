import { execSync } from 'child_process';

export default async function globalSetup() {
  execSync(
    'docker exec pjbl-app php artisan migrate:fresh --seed --force',
    { stdio: 'inherit' },
  );
}
