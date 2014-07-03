<?php

class JobsController extends BaseResource
{
    /**
     * 资源视图目录
     * @var string
     */
    protected $resourceView = 'account.jobs';

    /**
     * 资源模型名称，初始化后转为模型实例
     * @var string|Illuminate\Database\Eloquent\Model
     */
    protected $model = 'Jobs';

    /**
     * 资源标识
     * @var string
     */
    protected $resource = 'myjobs';

    /**
     * 资源数据库表
     * @var string
     */
    protected $resourceTable = 'jobs';

    /**
     * 资源名称（中文）
     * @var string
     */
    protected $resourceName = '招聘信息';

    /**
     * 自定义验证消息
     * @var array
     */
    protected $validatorMessages = array(
        'title.required'        => '请填写标题。',
        'title.unique'          => '已有同名标题。',
        'location.required'     => '请选择所在省份',
        'slug.unique'           => '已有同名 sulg。',
        'content.required'      => '请填写内容。',
        'category.exists'       => '请填选择正确的标题。',
    );

    /**
     * 资源列表页面
     * GET         /resource
     * @return Response
     */
    public function index()
    {
        // 获取排序条件
        $orderColumn = Input::get('sort_up', Input::get('sort_down', 'created_at'));
        $direction   = Input::get('sort_up') ? 'asc' : 'desc' ;
        // 获取搜索条件
        switch (Input::get('target')) {
            case 'title':
                $title = Input::get('like');
                break;
        }
        // 构造查询语句
        $query = $this->model->orderBy($orderColumn, $direction);
        isset($title) AND $query->where('title', 'like', "%{$title}%");
        $datas = $query->paginate(15);
        return View::make($this->resourceView.'.index')->with(compact('datas'));
    }

    /**
     * 资源创建页面
     * GET         /resource/create
     * @return Response
     */
    public function create()
    {
        $categoryLists = JobsCategories::lists('name', 'id');
        return View::make($this->resourceView.'.create')->with(compact('categoryLists'));
    }

    /**
     * 资源创建动作
     * POST        /resource
     * @return Response
     */
    public function store()
    {
        // 获取所有表单数据.
        $data   = Input::all();
        // 创建验证规则
        $unique = $this->unique();
        $rules  = array(
            'title'        => 'required|'.$unique,
            'content'      => 'required',
            'category'     => 'exists:jobs_categories,id',
            'location'     => 'required',
        );
        $slug      = Input::input('title');
        $hashslug  = date('H.i.s').'-'.md5($slug).'.html';
        // 自定义验证消息
        $messages  = $this->validatorMessages;
        // 开始验证
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->passes()) {
            // 验证成功
            // 添加资源
            $model                   = $this->model;
            $model->user_id          = Auth::user()->id;
            $model->category_id      = $data['category'];
            $model->title            = e($data['title']);
            $model->location         = e($data['location']);
            $model->slug             = $hashslug;
            $model->content          = e($data['content']);
            $model->meta_title       = e($data['title']);
            $model->meta_description = e($data['title']);
            $model->meta_keywords    = e($data['title']);

            if ($model->save()) {
                // 添加成功
                return Redirect::back()
                    ->with('success', '<strong>'.$this->resourceName.'添加成功：</strong>您可以继续添加新'.$this->resourceName.'，或返回'.$this->resourceName.'列表。');
            } else {
                // 添加失败
                return Redirect::back()
                    ->withInput()
                    ->with('error', '<strong>'.$this->resourceName.'添加失败。</strong>');
            }
        } else {
            // 验证失败
            return Redirect::back()->withInput()->withErrors($validator);
        }
    }

    /**
     * 资源编辑页面
     * GET         /resource/{id}/edit
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $data          = $this->model->find($id);
        $categoryLists = JobsCategories::lists('name', 'id');
        $jobs        = Jobs::where('slug', $data->slug)->first();
        return View::make($this->resourceView.'.edit')->with(compact('data', 'categoryLists', 'jobs'));
    }

    /**
     * 资源编辑动作
     * PUT/PATCH   /resource/{id}
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        // 获取所有表单数据.
        $data = Input::all();
        // 创建验证规则
        $rules  = array(
            'title'        => 'required',
            'content'      => 'required',
            'category'     => 'exists:jobs_categories,id',
            'location'     => 'required',
        );
        $slug = Input::input('title');
        $hashslug = date('H.i.s').'-'.md5($slug).'.html';
        // 自定义验证消息
        $messages = $this->validatorMessages;
        // 开始验证
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->passes()) {

            // 验证成功
            // 更新资源
            $model = $this->model->find($id);
            $model->user_id          = Auth::user()->id;
            $model->category_id      = $data['category'];
            $model->location         = e($data['location']);
            $model->title            = e($data['title']);
            $model->slug             = $hashslug;
            $model->content          = e($data['content']);
            $model->meta_title       = e($data['title']);
            $model->meta_description = e($data['title']);
            $model->meta_keywords    = e($data['title']);

            if ($model->save()) {
                // 更新成功
                return Redirect::back()
                    ->with('success', '<strong>'.$this->resourceName.'更新成功：</strong>您可以继续编辑'.$this->resourceName.'，或返回'.$this->resourceName.'列表。');
            } else {
                // 更新失败
                return Redirect::back()
                    ->withInput()
                    ->with('error', '<strong>'.$this->resourceName.'更新失败。</strong>');
            }
        } else {
            // 验证失败
            return Redirect::back()->withInput()->withErrors($validator);
        }
    }

    /**
     * 资源删除动作
     * DELETE      /resource/{id}
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $data = $this->model->find($id);
        if (is_null($data))
            return Redirect::back()->with('error', '没有找到对应的'.$this->resourceName.'。');
        elseif ($data)
        {
            $model      = $this->model->find($id);
            $thumbnails = $model->thumbnails;
            File::delete(public_path('uploads/jobs_thumbnails/'.$thumbnails));
            $data->delete();
            return Redirect::back()->with('success', $this->resourceName.'删除成功。');
        }
        else
            return Redirect::back()->with('warning', $this->resourceName.'删除失败。');
    }

    /**
     * 动作：添加资源图片
     * @return Response
     */
    public function postUpload($id)
    {
        $input = Input::all();
        $rules = array(
            'file' => 'image|max:3000',
        );

        $validation = Validator::make($input, $rules);

        if ($validation->fails())
        {
            return Response::make($validation->errors->first(), 400);
        }

        $file                = Input::file('file');
        $destinationPath     = 'uploads/jobs/';
        $ext                 = $file->guessClientExtension();  // Get real extension according to mime type
        $fullname            = $file->getClientOriginalName(); // Client file name, including the extension of the client
        $hashname            = date('H.i.s').'-'.md5($fullname).'.'.$ext; // Hash processed file name, including the real extension
        $picture             = Image::make($file->getRealPath());
        // crop the best fitting ratio and resize image
        $picture->fit(1024, 683)->save(public_path($destinationPath.$hashname));
        $picture->fit(585, 347)->save(public_path('uploads/jobs_thumbnails/'.$hashname));

        $model               = $this->model->find($id);
        $oldThumbnails       = $model->thumbnails;
        $model->thumbnails   = $hashname;
        $model->save();

        File::delete(public_path('uploads/jobs_thumbnails/'.$oldThumbnails));

        $models              = new JobsPictures;
        $models->filename    = $hashname;
        $models->jobs_id   = $id;
        $models->user_id     = Auth::user()->id;
        $models->save();

        if( $models->save() ) {
           return Response::json('success', 200);
        } else {
           return Response::json('error', 400);
        }
    }

    /**
     * 动作：删除资源图片
     * @return Response
     */
    public function deleteUpload($id)
    {
        // 仅允许对当前资源分享的封面图片进行删除操作
        $filename = JobsPictures::where('id', $id)->where('user_id', Auth::user()->id)->first();
        $oldImage = $filename->filename;

        if (is_null($filename))
            return Redirect::back()->with('error', '没有找到对应的图片');
        elseif ($filename->delete()) {

        File::delete(
            public_path('uploads/jobs/'.$oldImage)
        );
            return Redirect::back()->with('success', '图片删除成功。');
        }

        else
            return Redirect::back()->with('warning', '图片删除失败。');
    }

    /**
     * 页面：我的评论
     * @return Response
     */
    public function resume()
    {
        $resume = JobsResume::where('user_id', Auth::user()->id)->paginate(15);
        return View::make($this->resourceView.'.resume')->with(compact('resume'));
    }

    /**
     * 动作：删除我的评论
     * @return Response
     */
    public function deleteResume($id)
    {
        // 仅允许对自己的简历进行删除操作
        $resume = JobsResume::where('id', $id)->where('user_id', Auth::user()->id)->first();
        if (is_null($resume))
            return Redirect::back()->with('error', '没有找到对应的简历');
        elseif ($resume->delete())
            return Redirect::back()->with('success', '简历删除成功。');
        else
            return Redirect::back()->with('warning', '简历删除失败。');
    }

    /**
     * 页面：酷工作
     * @return Respanse
     */
    public function getIndex()
    {
        $jobs       = Jobs::orderBy('created_at', 'desc')->paginate(12);
        $categories = JobsCategories::orderBy('sort_order')->paginate(6);
        return View::make('jobs.index')->with(compact('jobs', 'categories', 'data'));
    }

    /**
     * 资源列表
     * @return Respanse
     */
    public function category($category_id)
    {
        $jobs             = Jobs::where('category_id', $category_id)->orderBy('created_at', 'desc')->paginate(6);
        $categories       = JobsCategories::orderBy('sort_order')->get();
        $current_category = JobsCategories::where('id', $category_id)->first();
        return View::make('jobs.category')->with(compact('jobs', 'categories', 'category_id', 'current_category'));
    }

    /**
     * 资源展示页面
     * @param  string $slug 缩略名
     * @return response
     */
    public function show($slug)
    {
        $jobs       = Jobs::where('slug', $slug)->first();
        is_null($jobs) AND App::abort(404);
        $categories = JobsCategories::orderBy('sort_order')->get();
        return View::make('jobs.show')->with(compact('jobs', 'categories'));
    }

    public function postResume($slug)
    {
        // 获取简历内容
        $content = e(Input::get('content'));
        // 字数检查
        if (mb_strlen($content)<3)
            return Redirect::back()->withInput()->withErrors($this->messages->add('content', '评论不得少于3个字符。'));
        // 查找对应招聘
        $jobs = Jobs::where('slug', $slug)->first();
        // 创建简历
        $resume = new JobsResume;
        $resume->content   = $content;
        $resume->jobs_id = $jobs->id;
        $resume->user_id   = Auth::user()->id;
        if ($resume->save()) {
            // 创建成功
            // 更新应聘者数
            $jobs->resume_count = $jobs->resume->count();
            $jobs->save();
            // 返回成功信息
            return Redirect::back()->with('success', '评论成功。');
        } else {
            // 创建失败
            return Redirect::back()->withInput()->with('error', '评论失败。');
        }
    }

}