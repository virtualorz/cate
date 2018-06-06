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
    public function list($use_sn = '') {

        $dataSet_cate = self::get_child_list($use_sn);

        return $dataSet_cate;
    }

    public function add($use_sn = '')
    {
        $validator = Validator::make(Request::all(), [
            'cate-name' => 'string|required|max:12',
            'cate-select_photo' => 'string|required',
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
            $order = 1;
            //處理排序問題
            if(Request::input('cate-order',0) == -1)
            { //位於開頭
                DB::table('cate')
                    ->update([
                        'order' => DB::raw('`order` +1')
                    ]);
            }
            else if(Request::input('cate-order',0) == 0)
            {//位於最後一個
                $data_last = DB::table('cate')
                    ->select([
                        'cate.order'
                    ])
                    ->orderBy('order','desc')
                    ->first();
                if($data_last != null)
                {
                    $order = $data_last->order + 1;
                }
            }
            else
            {//位於誰後面
                $data_order = DB::table('cate')
                    ->select([
                        'cate.order'
                    ])
                    ->where('cate.id',Request::input('cate-order',0))
                    ->first();
                if($data_order != null)
                {
                    $order = $data_order->order + 1;
                }
                DB::table('cate')
                    ->where('cate.id','>',Request::input('cate-order',0))
                    ->update([
                        'order' => DB::raw('`order` +1')
                    ]);
            }

            $insert_id = DB::table('cate')
                ->insertGetId([
                    'parent_id' => Request::input('cate-parent_id'),
                    'created_at' => $dtNow,
                    'updated_at' => $dtNow,
                    'name' => Request::input('cate-name'),
                    'select_photo' => Request::input('cate-select_photo'),
                    'order' => $order,
                    'use_sn' => $use_sn,
                    'enable' => Request::input('cate-enable'),
                    'creat_admin_id' => Request::input('cate-creat_admin_id', null),
                    'update_admin_id' => Request::input('cate-update_admin_id', null),
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
            $Fileupload = new Fileupload();
            $Fileupload->handleFile(Request::input('cate-select_photo', '[]'));

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function edit()
    {
        $validator = Validator::make(Request::all(), [
            'cate-name' => 'string|required|max:12',
            'cate-select_photo' => 'string|required',
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
                    'cate.select_photo',
                    'cate.order',
                ])
                ->where('cate.id',Request::input('id'))
                ->first();

            $order = $dataRow_before->order;

            //處理排序問題
            if(Request::input('cate-order',0) == -1)
            { //位於開頭
                DB::table('cate')
                    ->where('cate.order','<',$order)
                    ->update([
                        'order' => DB::raw('`order` +1')
                    ]);
                $order = 1;
            }
            else if(Request::input('cate-order',0) != 0)
            {//位於誰後面
                $data_order = DB::table('cate')
                    ->select([
                        'cate.order'
                    ])
                    ->where('cate.id',Request::input('cate-order',0))
                    ->first();
                if(Request::input('cate-order',0) > $order)
                {
                    DB::table('cate')
                        ->where('cate.order','>',$order)
                        ->where('cate.order','<=',$data_order->order)
                        ->update([
                            'order' => DB::raw('`order` -1')
                        ]);
                    $order = $data_order->order;
                }
                else
                {
                    DB::table('cate')
                        ->where('cate.order','>',$data_order->order)
                        ->where('cate.order','<',$order)
                        ->update([
                            'order' => DB::raw('`order` +1')
                        ]);
                    $order = $data_order->order +1;
                }
                
            }
            
            
            DB::table('cate')
                ->where('id', Request::input('id'))
                ->update([
                    'parent_id' => Request::input('cate-parent_id'),
                    'updated_at' => $dtNow,
                    'name' => Request::input('cate-name'),
                    'select_photo' => Request::input('cate-select_photo'),
                    'order' => $order,
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
            $Fileupload = new Fileupload();
            $Fileupload->handleFile(Request::input('cate-select_photo', '[]'), isset($dataRow_before->select_photo) ? $dataRow_before->select_photo : []);

            DB::commit();

        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function detail($id = '')
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
                    'cate.update_admin_id',
                    'parent_cate.name AS parent_name',
                ])
                ->LeftJoin('cate as parent_cate','cate.parent_id','=','parent_cate.id')
                ->where('cate.id', $id)
                ->whereNull('cate.delete')
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
                //$Fileupload = new Fileupload();
                //$dataRow_cate->select_photo = head($Fileupload->getFiles($dataRow_cate->select_photo));
            }
        } catch (\PDOException $ex) {
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }

        return $dataRow_cate;
    }

    public function delete()
    {
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
                
                $Fileupload = new Fileupload();
                $Fileupload->handleFile([], isset($dataRow_before->select_photo) ? $dataRow_before->select_photo : []);
            }

            DB::commit();
        } catch (\PDOException $ex) {
            DB::rollBack();
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }
    }

    public function enable($type = '')
    {
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
                throw new Exception($ex);
                \Log::error($ex->getMessage());
            }
        }
    }

    private function get_child_list($use_sn = '',$level = 0, $parent_id = null)
    {
        $level = $level +1;
        $dataSet_cate_backend = [];
        $dataSet_cate_front = [];
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
                $child_list = self::get_child_list($use_sn, $level, $v->id);
                
                $dataSet_cate[$k]->lang = $dataSet_lang;
                $dataSet_cate[$k]->chlid_list = $child_list[1];

                array_push($dataSet_cate_backend,$v);
                array_push($dataSet_cate_front,$v);
                $level_text = '';
                for($i=1;$i<=$level;$i++)
                {
                    $level_text .= '-';
                }
                foreach($child_list[1] as $k1=>$v1)
                {
                    $child_list[1][$k1]->name = $level_text . $v1->name;
                    array_push($dataSet_cate_backend,$child_list[1][$k1]);
                }
                
                if($v->enable == 1)
                {

                    array_push($dataSet_cate_front,$v);

                }
            }
        } catch (\PDOException $ex) {
            throw new PDOException($ex->getMessage());
            \Log::error($ex->getMessage());
        } catch (\Exception $ex) {
            throw new Exception($ex);
            \Log::error($ex->getMessage());
        }

        return [$dataSet_cate_front,$dataSet_cate_backend];
    }
}
