<?php

namespace Jw\Database\Dictionary\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jw\Database\Dictionary\Models\DatabaseDictionary;
use Jw\Database\Dictionary\Models\Table;
use Jw\Support\Tool\DataStructure;
use Jw\Support\Tool\StringTool;

class DatabaseDictionaryController extends Controller
{
    public function index(Request $request)
    {
        $databaseDictionary = DatabaseDictionary::when(!empty($request['type']), function ($query) use ($request) {
            return $query->where(['type' => $request['type']]);
        })->when(!empty($request['father_id']), function ($query) use ($request) {
            return $query->with('table_data')->where(['father_id' => $request['father_id']]);
        })
            ->orderBy('order', 'asc')
            ->get();

        return $databaseDictionary->toArray();
    }

    public function store(Request $request)
    {
        $databaseDictionary = new DatabaseDictionary();

        $databaseDictionary->title = $request->title ?: '';
        $databaseDictionary->order = $request->order ?: 0;
        $databaseDictionary->type = $request->type ?: 1;
        $databaseDictionary->father_id = $request->father_id ?: 0;

        $databaseDictionary->save();

        return ['ServerNo' => 200];
    }

    public function update(Request $request)
    {
        $databaseDictionary = DatabaseDictionary::findOrFail($request->id);

        $databaseDictionary->title = $request->title ?: '';
        $databaseDictionary->order = $request->order ?: 0;
        $databaseDictionary->type = $request->type ?: 1;

        $databaseDictionary->save();

        return ['ServerNo' => 200];
    }

    public function destroy(Request $request)
    {
        DatabaseDictionary::destroy($request->id);
        return ['ServerNo' => 200];
    }

    public function getAllTable()
    {
        $databaseDictionary = DatabaseDictionary::with('child')->where(['type' => 1])->get();

        $tableName = [''];
        $databaseDictionary->each(function ($item) use (&$tableName) {
            $item->child->each(function ($item) use (&$tableName) {
                $tableName[] = $item->title;
            });
        });

        return Table::whereNotIn('TABLE_NAME', $tableName)->get();
    }

    public function tableConstruct(Request $request)
    {
        $tableConstruct = DB::select('show full columns from ' . $request->tableName);
        $tableConstruct = (new Collection($tableConstruct))->map(function ($item) {
            $item->Default = StringTool::valueView($item->Default);
            if (strpos($item->Type, 'int') !== false ||
                strpos($item->Type, 'double') !== false ||
                strpos($item->Type, 'float') !== false ||
                strpos($item->Type, 'decimal') !== false ||
                strpos($item->Type, 'numeric') !== false) {
                $item->Default = (int)$item->Default;
            }
            return $item;
        });

        return $tableConstruct;
    }

    public function markdown()
    {
        $modules = DatabaseDictionary::with('child')->where(['type' => 1])->orderBy('order', 'asc')->get();

        $modules->each(function ($module) {
            $fileName = $module->title . '.md';
            $content = '';
            $module->child->each(function ($child) use (&$content) {
                $tableConstruct = DB::select('show full columns from ' . $child->title);

                $content .= '#### ' . $child->order . '. ' . $child->title . ' ' . $child->table_data->TABLE_COMMENT . "\r\n\r\n";
                $content .= "|Field|Type|Null|Key|Default|Extra|Comment|\r\n";
                $content .= "|---|---|---|---|---|---|---|\r\n";
                foreach ($tableConstruct as $value) {
                    $content .= "|{$value->Field}|{$value->Type}|{$value->Null}|{$value->Key}|{$value->Default}|{$value->Extra}|{$value->Comment}|\r\n";
                }
                $content .= "\r\n";
            });
            if (!\File::exists(storage_path('database/'))) {
                mkdir(storage_path('database/'));
            }
            \File::put(storage_path('database/' . $fileName), $content);
        });
        return ['ServerNo' => 200];
    }

    public function helper(Request $request)
    {
        $table = Table::where('TABLE_NAME', $request->tableName)->first();

        if (empty($table)) {
            return response()->json(['ServerNo' => 400, 'ServerMsg' => '表不存在']);
        }

        if ($request->where) {
            $where = explode(',', $request->where);
        } else {
            $where = null;
        }

        $models = DB::table($request->tableName)->when($where, function ($query) use ($where, $request) {
            $fields = DB::select('show full columns from ' . $request->tableName);
            $primaryKey = '';
            array_map(function ($item) use (&$primaryKey) {
                if ($item->Key == 'PRI') {
                    $primaryKey = $item->Field;
                }
            }, $fields);
            return $query->whereIn($primaryKey, $where);
        });
        if (is_array($where) && count($where) > 1) {
            $models = $models->get();
        } else {
            $models = $models->first();
        }

        if (empty($models)) {
            return response()->json(['ServerNo' => 400, 'ServerMsg' => '没有对应的数据,请检查数据库']);
        }
        switch ($request->selectType) {
            case 'json':
                return response()->json([
                    'ServerNo' => 200,
                    'ServerData' => DataStructure::getJsonView($models, true, 4, ':')
                ]);
                break;
            case 'phpArray':
                return response()->json([
                    'ServerNo' => 200,
                    'ServerData' => DataStructure::getJsonView($models, true, 4, '=>', true)
                ]);
                break;
            case 'swagger':
                if ($models instanceof Collection && count($models) > 1) {
                    $models = $models[0];
                }
                $response = $this->getSwaggerView($request, $models, $table);
                return response()->json([
                    'ServerNo' => 200,
                    'ServerData' => $response
                ]);
                break;
            default:
                return response()->json(['ServerNo' => 400, 'ServerMsg' => '系统异常']);
        }
    }

    /**
     * @param $request
     * @param $model
     * @param $table
     * @return string
     * @Author jiaWen.chen
     */
    private function getSwaggerView($request, $model, $table)
    {
        $schema = [
            'schema' => null,
            'title' => null,
            'description' => null,
            'type' => 'object',
            'required' => [],
            'property' => [
                [
                    'property' => null,
                    'type' => null,
                    'default' => null,
                    'example' => null,
                    'nullable' => null,
                    'deprecated' => null,
                    'description' => null,
                ]
            ],
        ];

        $schema['schema'] = Str::studly(Str::camel($table->TABLE_NAME));
        $schema['title'] = $schema['schema'] . ' Model';
        $schema['description'] = $table->TABLE_COMMENT;

        $fields = DB::select('show full columns from ' . $request->tableName);
        $fields = new Collection($fields);

        $fields->each(function ($item, $key) use (&$schema, $model) {
            $schema['property'][$key]['property'] = $item->Field;

            if (strpos($item->Type, 'int') !== false) {
                $schema['property'][$key]['type'] = 'integer';
            } else if (strpos($item->Type, 'double') !== false ||
                strpos($item->Type, 'float') !== false ||
                strpos($item->Type, 'decimal') !== false ||
                strpos($item->Type, 'numeric') !== false
            ) {
                $schema['property'][$key]['type'] = 'float';
            } else if (strpos($item->Type, 'text') !== false ||
                strpos($item->Type, 'char') !== false) {
                $schema['property'][$key]['type'] = 'string';
            } else if (strpos($item->Type, 'time') !== false) {
                $schema['property'][$key]['type'] = 'dateTime';
            } else if (strpos($item->Type, 'date') !== false) {
                $schema['property'][$key]['type'] = 'date';
            }

            $schema['property'][$key]['default'] = $item->Default;
            $tmp = $item->Field;
            $schema['property'][$key]['example'] = $model->$tmp;
            $schema['property'][$key]['nullable'] = $item->Null == "YES" ? true : false;
            if (!$schema['property'][$key]['nullable']) {
                $schema['required'][] = $item->Field;
            }
            $schema['property'][$key]['deprecated'] = false;
            $schema['property'][$key]['description'] = $item->Comment;
        });


        $response[] = "@OA\Schema(\n";
        $response[] = $this->tabStr(1) . 'schema=' . StringTool::valueView($schema['schema']) . ",\n";
        $response[] = $this->tabStr(1) . 'title=' . StringTool::valueView($schema['title']) . ",\n";
        $response[] = $this->tabStr(1) . 'description=' . StringTool::valueView($schema['description']) . ",\n";
        $response[] = $this->tabStr(1) . 'type=' . StringTool::valueView($schema['type']) . ",\n";
        $response[] = $this->tabStr(1) . 'required={"' . implode('","', $schema['required']) . "\"},\n";
        foreach ($schema['property'] as $item) {
            $tmp = $this->tabStr(1) . "@OA\Property(";
            $tmp .= 'property=' . StringTool::valueView($item['property']) . ', ';
            $tmp .= 'type=' . StringTool::valueView($item['type']) . ', ';
            $tmp .= 'default=' . StringTool::valueView($item['default']) . ', ';
            $tmp .= 'example=' . StringTool::valueView($item['example']) . ', ';
            $tmp .= 'nullable=' . StringTool::valueView($item['nullable']) . ', ';
            $tmp .= 'deprecated=' . StringTool::valueView($item['deprecated']) . ', ';
            $tmp .= 'description=' . StringTool::valueView($item['description']) . ' ';
            $tmp .= "),\n";

            $response = array_merge($response, [$tmp]);
        }
        $response[] = ")\n";
        $response = array_map(function ($item) {
            return ' * ' . $item;
        }, $response);
        array_unshift($response, "/** \n");
        array_push($response, " */\n");

        return implode("", $response);
    }

    private function tabStr($count = 1)
    {
        $response = '';
        for ($i = 0; $i < $count * 4; $i++) {
            $response .= ' ';
        }
        return $response;
    }
}
