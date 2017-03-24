@extends('layouts.app')
@section('content')
    <h2>Linked Accounts</h2>
    <table class="table table-striped table-bordered">
        <tr>
            <th>Local Account</th>
            <th>Office 365 Account</th>
            <th></th>
        </tr>

        @if(count($users)>0)
            @foreach($users as $user)
                <tr>
                    <td>{{$user->email}}</td>
                    <td>{{$user->o365Email}}</td>
                    <td>
                        <a href="/admin/unlinkaccounts/{{$user->id}}">Unlink</a>
                    </td>
                </tr>
        @endforeach
        @else
            <tr>
                <td class="empty-result" colspan="3">No linked accounts</td>
            </tr>
        @endif


    </table>
    <p><a class="btn btn-default" href="/admin">Return</a></p>

@endsection