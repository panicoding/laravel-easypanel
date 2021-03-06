<?php

namespace EasyPanel\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;

class MakeCRUD extends Command
{

    protected $signature = 'panel:crud {name?} {--f|force : Force mode}';

    protected $description = 'Create all action for CRUDs';

    public function handle()
    {
        $names = $this->argument('name') ? [$this->argument('name')] : config('easy_panel.actions', []);
        if($names == null) {
            throw new CommandNotFoundException("There is no action in config file");
        }
        foreach ($names as $name) {
            $config = config('easy_panel.crud.' . $name);
            if (!$config) {
                throw new CommandNotFoundException("There is no {$name} in config file");
            }

            $this->modelNameIsCorrect($name, $config['model']);

            if (!$config['create']) {
                $this->warn("The create action is disabled for {$name}");
            } else {
                $this->call('panel:create', ['name' => $name, '--force' => $this->option('force')]);
            }

            if (!$config['update']) {
                $this->warn("The update action is disabled for {$name}");
            } else {
                $this->call('panel:update', ['name' => $name, '--force' => $this->option('force')]);
            }

            $this->call('panel:read', ['name' => $name, '--force' => $this->option('force')]);
            $this->call('panel:single', ['name' => $name, '--force' => $this->option('force')]);
        }
    }

    private function modelNameIsCorrect($name, $model)
    {
        $model = explode('\\', $model);
        $model = strtolower(end($model));

        if($model != $name){
            throw new CommandNotFoundException("Action key should be equal to model name, You are using {$name} as key name but your model name is {$model}");
        }
    }

}
