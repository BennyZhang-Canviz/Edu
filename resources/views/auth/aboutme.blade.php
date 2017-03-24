@extends('layouts.app')

@section('content')
    <div class="container ">
        <h2>About Me</h2>
        <div class="margin-top-12 margin-btm-12 aboutme">
            <b>Username:</b><br />
            {{$displayName}}
        </div>
        <div class="margin-top-12 margin-btm-12">

            <b>Logged in as:</b><br /> {{$role}}
        </div>

            <div class="margin-btm-12">
                @if($favoriteColor)
                    <b>Favorite Color:</b>
                <br/>
                <form method="post" action="/auth/savefavoritecolor">
                    {{ csrf_field() }}
                    <select name="FavoriteColor" id="FavoriteColor"  aria-invalid="false">
                        <option value="#2F19FF" {{$favoriteColor==='#2F19FF'?'selected':''}}>Blue</option>
                        <option value="#127605" {{$favoriteColor==='#127605'?'selected':''}}>Green</option>
                        <option value="#535353" {{$favoriteColor==='#535353'?'selected':''}}>Grey</option>
                    </select>
                    <input type="submit" value="Save">
                    @if($showSaveMessage)
                        <span class="saveresult">Favorite color has been updated!</span>
                    @endif
                </form>
                @endif


            </div>

            <div class="margin-btm-12 ">
                <b>Classes:</b>
                <br />
                <div>

                </div>
            </div>
    </div>
@endsection
