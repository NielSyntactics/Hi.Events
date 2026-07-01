#!/usr/bin/env node

const { exec, execFile } = require('child_process');
const path = require('path');

const dockerPath = path.join(__dirname, '../docker/development');
const isWindows = process.platform === 'win32';

function runCommand(command, cwd = process.cwd()) {
  return new Promise((resolve, reject) => {
    const options = {
      cwd,
      shell: isWindows ? true : '/bin/bash',
      maxBuffer: 10 * 1024 * 1024  // 10MB buffer
    };

    const child = exec(command, options, (error, stdout, stderr) => {
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

    // Use appropriate startup script for Windows vs Unix
    const startupCmd = isWindows
      ? 'powershell -NoProfile -ExecutionPolicy Bypass -File ./start-dev.ps1'
      : 'bash ./start-dev.sh';

    await runCommand(startupCmd, dockerPath);

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
