@extends('beautymail::templates.widgets')

@section('content')


    @include('beautymail::templates.widgets.newfeatureStart')

    <h4 class="secondary"><strong>{!! $eMessage == '' ? trans('laravel-modules-base::admin.email.error.info_title') : $eMessage !!}</strong></h4>

    <table>
        <tbody>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.date') !!} : </b></td>
            <td>{!! $date !!}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.id') !!} : </b></td>
            <td>{!! $rUser->id or '-' !!}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.fullname') !!} : </b></td>
            <td>{!! $rUser->fullname or '-' !!}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.email') !!} : </b></td>
            <td>{!! $rUser->email or '-' !!}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.old_session') !!} : </b></td>
            <td><pre><?php print_r($rSessionOld); ?></pre></td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.new_session') !!} : </b></td>
            <td><pre><?php print_r($rSessionNew); ?></pre></td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.request_all') !!} : </b></td>
            <td><pre><?php print_r($rAll); ?></pre></td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.ip') !!} : </b></td>
            <td>{{ $rIp }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.decoded_path') !!} : </b></td>
            <td>{{ $rDecodedPath }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.path') !!} : </b></td>
            <td>{{ $rPath }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.full_url') !!} : </b></td>
            <td>{{ $rFullUrl }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.url') !!} : </b></td>
            <td>{{ $rUrl }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.domain') !!} : </b></td>
            <td>{{ $rRoot }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.method') !!} : </b></td>
            <td>{{ $rMethod }}</td>
        </tr>
        </tbody>
    </table>

    @include('beautymail::templates.widgets.newfeatureEnd')

    @include('beautymail::templates.widgets.articleStart')

    <h4 class="secondary"><strong>{!! trans('laravel-modules-base::admin.email.error.title') !!}</strong></h4>
    <table>
        <tbody>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.message') !!} : </b></td>
            <td>{{ $eMessage }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.code') !!} : </b></td>
            <td>{{ $eCode }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.file') !!} : </b></td>
            <td>{{ $eFile }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.line') !!} : </b></td>
            <td>{{ $eLine }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.previous') !!} : </b></td>
            <td>{{ $ePrevious }}</td>
        </tr>
        <tr>
            <td width="150" valign="top"><b>{!! trans('laravel-modules-base::admin.email.error.trace') !!} : </b></td>
            <td>{!! $eTrace !!}</td>
        </tr>
        </tbody>
    </table>

    @include('beautymail::templates.widgets.articleEnd')

@stop