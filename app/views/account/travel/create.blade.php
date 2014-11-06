@include('layout.account-header')
@yield('content')
{{ script('ckeditor') }}
<body id="inbox-page" class="bg-gray-light">


	@include('layout.account-navigation')
	@yield('content')

	@include('layout.account-sidebar')
	@yield('content')

	<div class="preloader">
		<div class="timer"></div>
	</div>


	<div id="container" class="main-content tp-t-60">

		<button class="menu-btn btn btn-bordered text-gray-alt text-bold top-left-corner tm-l-30 pull-left">&#9776; 菜单</button>

		<div class="row">

			<div class="col-sm-9">
				<div class="bg-white p-tb-30">

					<div class="btn-group">
						<div class="iconmelon m-r-10 m-l-30">
							<svg viewBox="0 0 32 32">
								<g filter="">
									<use xlink:href="#speech-talk-user"></use>
								</g>
							</svg>
						</div>

						<span class="text-gray-dark text-large align-with-button m-r-30">
								添加新{{ $resourceName }}
						</span>

					</div>


					<div class="pull-right m-r-30 mail-nav">

						<a href="{{ route('mytravel.index') }}" class="btn btn-bordered text-gray-alt">
							返回{{ $resourceName }}列表
						</a>

					</div>

					<div class="p-lr-30 p-tb-10 pm-lr-10">
						@include('layout.notification')
					</div>

					<div class="p-lr-30 p-tb-10 pm-lr-10">

						<ul class="nav nav-tabs">
							<li class="active">
								<a href="#tab-general" data-toggle="tab">
									<div class="text-small">Main Content</div>
									<span class="text-uppercase">主要内容</span>
								</a>
							</li>
							<li>
								<a href="#tab-images-management" data-toggle="tab">
									<div class="text-small">Photo Management</div>
									<span class="text-uppercase">照片管理</span>
								</a>
							</li>
						</ul>

						<form class="form-horizontal" method="post" action="{{ route($resource.'.store', $data->id) }}" autocomplete="off" style="padding:1em;border:1px solid #ddd;border-top:0;" accept-charset="UTF-8" enctype="multipart/form-data">
							{{-- CSRF Token --}}
							<input type="hidden" name="_token" value="{{ csrf_token() }}" />

							{{-- Tabs Content --}}
							<div class="tab-content">

								{{-- General tab --}}
								<div class="tab-pane active" id="tab-general" style="margin:0 1em;">

									<div class="form-group">
										<label for="category">{{ $resourceName }}分类</label>
										{{ $errors->first('category', '<span style="color:#c7254e;margin:0 1em;">:message</span>') }}
										{{ Form::select('category', $categoryLists, 1, array('class' => 'form-control input-sm selectpicker input-light brad')) }}
									</div>

									<div class="form-group">
										<label for="title">{{ $resourceName }}标题</label>
										{{ $errors->first('title', '<span style="color:#c7254e;margin:0 1em;">:message</span>') }}
										<input class="form-control" type="text" name="title" id="title" value="{{ Input::old('title') }}" />
									</div>

									<div class="form-group">
										<label for="content">内容</label>
										{{ $errors->first('content', '<span style="color:#c7254e;margin:0 1em;">:message</span>') }}
										<textarea rows="10" id="content" class="ckeditor form-control" name="content" rows="10">{{ Input::old('content') }}</textarea>
									</div>

								</div>

								{{-- Images Management tab --}}
								<div class="tab-pane fade p-t-30" id="tab-images-management" style="margin:0 1em;">

									<div class="table-responsive form-group">
										<table class="table table-striped table-bordered table-hover">
											<thead>
												<tr>
													<th style="width:4em;text-align:center;">
														<div class="icheckbox_flat-blue" aria-checked="false" aria-disabled="false">
															<input type="checkbox" name="notification" value="" class="icheck">
															<ins class="iCheck-helper"></ins>
														</div>
													</th>
													<th>图片</th>
													<th style="width:5em;text-align:center;">操作</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($travel->pictures as $picture)
												<tr>
													<td style="text-align:center; padding: 50px 0;">
														<div class="icheckbox_flat-blue" aria-checked="false" aria-disabled="false">
															<input type="checkbox" name="notification" value="" class="icheck">
															<ins class="iCheck-helper"></ins>
														</div>
													</td>
													<td>
														<img width="100" height="100" src="{{ route('home') }}/uploads/travel/{{ $picture->filename }}">
													</td>
													<td style="text-align:center; padding: 50px 0;">
														<a href="javascript:void(0)" class="btn btn-xs btn-danger"
														onclick="modal('{{ route($resource.'.deleteUpload', $picture->id) }}')">删除图片</a>
													</td>
												</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div>

							</div>

							{{-- Form Actions --}}
							<div class="control-group p-b-10">
								<div class="controls">
									<button type="reset" class="btn btn-bordered text-gray-alt">清 空</button>
									<button type="submit" class="btn btn-success">保 存</button>
								</div>
							</div>
						</form>

					</div>

					<div class="p-lr-30 p-tb-10 pm-lr-10">上传图片</div>
					<div class="p-lr-30 p-tb-10 pm-lr-10">
						<form action="{{ route($resource.'.postUpload', $data->id) }}" class="dropzone" id="upload" method="post">
							<input type="hidden" name="_token" value="{{ csrf_token() }}" />
						</form>
					</div>

				</div>
			</div>
			{{-- /.col-lg-9 --}}

			@include('layout.account-slider')
			@yield('content')

		</div>
		{{-- /.row --}}

	</div>

	@include('layout.account-chat')
	@yield('content')

	<?php
	$modalData['modal'] = array(
		'id'      => 'myModal',
		'title'   => '系统提示',
		'message' => '确认删除此图片？',
		'footer'  =>
			Form::open(array('id' => 'real-delete', 'method' => 'delete')).'
				<button type="button" class="btn btn-sm btn-default" data-dismiss="modal">取消</button>
				<button type="submit" class="btn btn-sm btn-danger">确认删除</button>'.
			Form::close(),
	);
	?>
	@include('layout.modal', $modalData)
	<script>
		function modal(href)
		{
			$('#real-delete').attr('action', href);
			$('#myModal').modal();
		}
	</script>
</body>

</html>