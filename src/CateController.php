<?php
namespace Virtualorz\Cate;

use DB;
use Request;
use Validator;
use Virtualorz\Fileupload\Fileupload;
use App\Exceptions\ValidateException;
use PDOException;
use Exception;

class Cate
{
    public static function list($use_sn = '') {
        
        $dataSet_cate = self::get_child_list($use_sn);

        return $dataSet_cate;
    }

    public static function add()
    {
        $message = "OK";
        $validator = Validator::make(Request::all(), [
            'cate-name' => 'string|required|max:12',
            'cate-select_photo' => 'string|required',
            'cate-use_sn' => 'string|required|max:3',
            'cate-enable' => 'integer|required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        foreach (Request::input('cate-lang', []) as $k => $v) {
            $validator = Validator::make($v, [
                'cate-name' => 'string|required|max:12',
                'cate-select_photo' => 'string|required',
            ]);
            if ($validator->fails()) {
                throw new ValidateException($validator->errors());
            }
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {
            $insert_id = DB::table('cate')
                ->insertGetId([
                    'parent_id' => Request::input('cate-parent_id'),
                    'created_at' => $dtNow,
                    'updated_at' => $dtNow,
                    'name' => Request::input('cate-name'),
                    'select_photo' => Request::input('cate-select_photo'),
                    'order' => 0,
                    'use_sn' => Request::input('cate-use_sn'),
                    'enable' => Request::input('cate-enable'),
                    'creat_admin_id' => Request::input('cate-creat_admin_id', null),
                    'update_admin_id' => Request::input('cate-update_admin_id', null),
                ]);
            DB::table('cate')
                ->where('id', $insert_id)
                ->update([
                    'order' => $insert_id,
                ]);
            foreach (Request::input('cate-lang', []) as $k => $v) {
                DB::table('cate_lang')
                    ->insert([
                        'cate_id' => $insert_id,
                        'lang' => $k,
                        'created_at' => $dtNow,
                        'updated_at' => $dtNow,
                        'name' => $v['cate-name'],
                        'select_photo' => $v['cate-select_photo'],
                        'creat_admin_id' => Request::input('cate-creat_admin_id', null),
                        'update_admin_id' => Request::input('cate-update_admin_id', null),
                    ]);
            }
            Fileupload::handleFile(Request::input('cate-select_photo', '[]'));

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());
            \Log::error($ex->getMessage());
        }

        return $message;

    }

    public static function edit()
    {
        $message = "OK";
        $validator = Validator::make(Request::all(), [
            'cate-name' => 'string|required|max:12',
            'cate-select_photo' => 'string|required',
            'cate-use_sn' => 'string|required|max:3',
            'cate-enable' => 'integer|required',
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        foreach (Request::input('cate-lang', []) as $k => $v) {
            $validator = Validator::make($v, [
                'cate-name' => 'string|required|max:12',
                'cate-select_photo' => 'string|required',
            ]);
            if ($validator->fails()) {
                throw new ValidateException($validator->errors());
            }
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {
            $dataRow_before = DB::table('cate')
                ->select([
                    'cate.select_photo'
                ])
                ->where('cate.id',Request::input('id'))
                ->first();
            DB::table('cate')
                ->where('id', Request::input('id'))
                ->update([
                    'parent_id' => Request::input('cate-parent_id'),
                    'updated_at' => $dtNow,
                    'name' => Request::input('cate-name'),
                    'select_photo' => Request::input('cate-select_photo'),
                    'enable' => Request::input('cate-enable'),
                    'update_admin_id' => Request::input('cate-update_admin_id', null),
                ]);
            foreach (Request::input('cate-lang', []) as $k => $v) {
                DB::table('cate_lang')
                    ->where('cate_id', Request::input('id'))
                    ->where('lang', $k)
                    ->update([
                        'updated_at' => $dtNow,
                        'name' => $v['cate-name'],
                        'select_photo' => $v['cate-select_photo'],
                        'update_admin_id' => Request::input('cate-update_admin_id', null),
                    ]);
            }
            Fileupload::handleFile(Request::input('cate-select_photo', '[]'), isset($dataRow_before->select_photo) ? $dataRow_before->select_photo : []);

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());
            \Log::error($ex->getMessage());
        }

        return $message;
    }

    public static function detail($id = '')
    {
        $dataRow_cate = collect();
        try {
            $dataRow_cate = DB::table('cate')
                ->select([
                    'cate.id',
                    'cate.parent_id',
                    'cate.created_at',
                    'cate.updated_at',
                    'cate.name',
                    'cate.select_photo',
                    'cate.enable',
                ])
                ->where('id', $id)
                ->whereNull('delete')
                ->first();
            if ($dataRow_cate != null) {
                $dataSet_lang = DB::table('cate_lang')
                    ->select([
                        'cate_lang.lang',
                        'cate_lang.created_at',
                        'cate_lang.updated_at',
                        'cate_lang.name',
                        'cate_lang.select_photo',
                    ])
                    ->where('cate_lang.cate_id', $dataRow_cate->id)
                    ->get()
                    ->keyBy('lang');
                $dataRow_cate->lang = $dataSet_lang;
            }
        } catch (\PDOException $ex) {
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
            \Log::error($ex->getMessage());
        }

        return $dataRow_cate;
    }

    public static function delete($id = '')
    {
        $message = "OK";
        $validator = Validator::make(Request::all(), [
            'id' => 'required', //id可能是陣列可能不是
        ]);
        if ($validator->fails()) {
            throw new ValidateException($validator->errors());
        }

        $ids = Request::input('id', []);
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        $dtNow = new \DateTime();

        DB::beginTransaction();
        try {
            foreach ($ids as $k => $v) {
                $dataRow_before = DB::table('cate')
                ->select([
                    'cate.select_photo'
                ])
                ->where('cate.id',$v)
                ->first();

                DB::table('cate')
                    ->where('id', $v)
                    ->update([
                        'delete' => $dtNow,
                    ]);
            
                Fileupload::handleFile([], isset($dataRow_before->select_photo) ? $dataRow_before->select_photo : []);
            }

            DB::commit();
        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex->getMessage());
            \Log::error($ex->getMessage());
        }

        return $message;
    }

    public static function enable($type = '')
    {
        $message = "OK";
        if ($type !== '') {
            $validator = Validator::make(Request::all(), [
                'id' => 'required', //id可能是陣列可能不是
            ]);
            if ($validator->fails()) {
                throw new ValidateException($validator->errors());
            }

            $ids = Request::input('id', []);
            if (!is_array($ids)) {
                $ids = [$ids];
            }

            $dtNow = new \DateTime();

            DB::beginTransaction();
            try {
                foreach ($ids as $k => $v) {
                    DB::table('cate')
                        ->where('id', $v)
                        ->whereNull('delete')
                        ->update([
                            'enable' => $type,
                            'updated_at' => $dtNow,
                        ]);
                }
                DB::commit();
            } catch (\PDOException $ex) {
                DB::rollBack();
                throw new PDOException($ex->getMessage());
                \Log::error($ex->getMessage());
            } catch (\Exception $ex) {
                DB::rollBack();
                throw new Exception($ex->getMessage());
                \Log::error($ex->getMessage());
            }
        }

        return $message;
    }

    private static function get_child_list($use_sn = '', $parent_id = null)
    {
        $dataSet_cate = collect();
        try {
            $dataSet_cate = DB::table('cate')
                ->select([
                    'cate.id',
                    'cate.parent_id',
                    'cate.created_at',
                    'cate.updated_at',
                    'cate.name',
                    'cate.enable',
                    'cate.select_photo',
                ])
                ->where('use_sn', $use_sn)
                ->where('parent_id', $parent_id)
                ->where('enable', 1)
                ->whereNull('delete')
                ->orderBy('cate.order')
                ->get();
            foreach ($dataSet_cate as $k => $v) {
                $dataSet_lang = DB::table('cate_lang')
                    ->select([
                        'cate_lang.lang',
                        'cate_lang.created_at',
                        'cate_lang.updated_at',
                        'cate_lang.name',
                        'cate_lang.select_photo',
                    ])
                    ->where('cate_lang.cate_id', $v->id)
                    ->get()
                    ->keyBy('lang');
                $dataSet_cate[$k]->lang = $dataSet_lang;
                $child_list = self::get_child_list($use_sn, $v->id);
                $dataSet_cate[$k]->chlid_list = $child_list;
            }
        } catch (\PDOException $ex) {
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
            \Log::error($ex->getMessage());
        }

        return $dataSet_cate;
    }
}
