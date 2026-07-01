#!/usr/bin/env node

const { exec } = require('child_process');
const path = require('path');

const dockerPath = path.join(__dirname, '../docker/development');
const isWindows = process.platform === 'win32';
const shell = isWindows ? 'bash' : '/bin/bash';

function runCommand(command, cwd = process.cwd()) {
  return new Promise((resolve, reject) => {
    const child = exec(command, { cwd, shell }, (error, stdout, stderr) => {
      if (error) {
        reject(new Error(`${command} failed: ${error.message}`));
      } else {
        resolve(stdout);
      }
    });

    child.stdout?.pipe(process.stdout);
    child.stderr?.pipe(process.stderr);
  });
}

async function setup() {
  try {
    console.log('\n🐳 Starting Docker containers...\n');
    await runCommand('bash ./start-dev.sh', dockerPath);

    console.log('\n⏳ Waiting for containers to be ready...\n');
    await new Promise(r => setTimeout(r, 5000));

    console.log('\n📊 Running migrations...\n');
    await runCommand('docker compose -f docker-compose.dev.yml exec -T backend php artisan migrate', dockerPath);

    console.log('\n🔧 Generating domain objects...\n');
    await runCommand('docker compose -f docker-compose.dev.yml exec -T backend php artisan generate-domain-objects', dockerPath);

    console.log('\n✅ Setup complete! Frontend dev server starting...\n');
  } catch (error) {
    console.error('\n❌ Setup failed:', error.message);
    process.exit(1);
  }
}

setup();
