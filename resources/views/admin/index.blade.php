@extends('layouts.app')
@section('content')
    @if(session('msg') || $msg)
        <div class="message-container bg-danger"> <p>{{session('msg') }} <?php echo $msg; ?></p>  </div>
    @endif
    <h2>Admin</h2>
    @if (!$IsAdminConsented)
    <div>
        <h3>Admin Consent</h3>
        <hr />

        <p>To use this application in this tenancy you must first provide Admin Consent. </p>
        <p>Please click the button below to proceed.</p>

        <div class="form-group">
            <form method="post" action="{{url('/admin/adminconsent')}}">
            {{csrf_field()}}
            <input type="submit" value="Consent" class="btn btn-primary" />
        </form>
        </div>

    </div>
    @else
       <p>Admin Consent has been applied.</p>
        <hr/>
       <p>In some cases, you need to re-apply Admin Consent. For example, after the permissions of the AAD application change.</p>
       <p>Please click the button below to proceed.</p>
       <div class="form-group">
       <form method="post" action="{{url('/admin/adminconsent')}}">
           {{csrf_field()}}
           <input type="submit" value="Consent" class="btn btn-primary" />
       </form>

       <p>Please click the button below to cancel the admin consent.</p>
       <form method="post" action="{{url('/admin/adminunconsent')}}">
           {{csrf_field()}}
           <input type="submit" value="Admin Unconsent" class="btn btn-primary" />
       </form>
       <p>Note: It will take a few minutes to effect.</p>
       </div>
    @endif
@endsection