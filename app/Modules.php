<?php

namespace App;

use App\Games\Kernel\Module\Module;
use Jenssegers\Mongodb\Eloquent\Model;

class Modules extends Model {

    protected $collection = 'modules';
    protected $connection = 'mongodb';

    protected $fillable = [
        'game', 'demo', 'data'
    ];

    protected $casts = [
        'data' => 'json'
    ];

    public static function get(\App\Games\Kernel\Game $game, bool $demo): ModuleData {
        if(Modules::where('game', $game->metadata()->id())->where('demo', $demo)->first() == null) {
            Modules::create([
                'game' => $game->metadata()->id(),
                'demo' => $demo,
                'data' => []
            ]);
        }
        return new ModuleData($game, Modules::where('game', $game->metadata()->id())->where('demo', $demo)->first());
    }

}

class ModuleData {

    private \App\Games\Kernel\Game $game;
    private Modules $model;
    private array $data;

    public function __construct(\App\Games\Kernel\Game $game, Modules $model) {
        $this->game = $game;
        $this->model = $model;
        $this->data = $model->data;
    }

    public function activeModules($data = null, $pfResult = null, Game $game = null): array {
        $active = []; $result = [];
        foreach($this->data as $module_id => $value) array_push($active, $module_id);

        foreach (Module::modules() as $module) {
            $instance = new $module($this->game, $game, $data, $pfResult);
            if(in_array($instance->id(), $active)) array_push($result, $instance);
        }

        return $result;
    }

    public function isEnabled(Module $module) {
        return isset($this->data[$module->id()]);
    }

    public function toggleModule(Module $module) {
        if(!isset($this->data[$module->id()])) {
            $fill = [];
            foreach($module->settings() as $setting) $fill = array_merge($fill, [$setting->id() => $setting->defaultValue()]);

            $this->data = array_merge($this->data, [
                $module->id() => $fill
            ]);
        }
        else unset($this->data[$module->id()]);
        return $this;
    }

    public function set(Module $module, string $option_key, string $value) {
        $this->data[$module->id()][$option_key] = $value;
        return $this;
    }

    public function get(Module $module, string $option_key) {
        return $this->data[$module->id()][$option_key] ?? '';
    }

    public function save() {
        $this->model->update(['data' => $this->data]);
    }

}
