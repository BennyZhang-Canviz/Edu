@extends('layouts.app')
@section('content')
    <div class="container body-content">
        <h2>Unlink Accounts</h2>
        <h3>Are you sure you want to unlink the accounts?</h3>
        <div>
            <hr>
            <dl class="dl-horizontal"></dl>
            <form action="/Admin/UnlinkAccounts/60605099-7051-43ac-ac62-90984fa31dd1" method="post"><input
                        name="__RequestVerificationToken" type="hidden"
                        value="4rxqG5FLQyKwqW43w7t799l7-J9JACseGyIzFP8pEY6KCGMGgwmgGUp-5_4hZKKoOezIg1ZjeZLlPZCoNDoidZxPE7GTTqOnFYDxfl44sv9IrstWTyoAeENrU5JZE5dBdIEot82FyNTzpp1fuV1jGg2">
                <p>Local Account: admin@canvizEDU.onmicrosoft.com</p>
                <p>Office 365 Account: admin@canvizEDU.onmicrosoft.com</p>
                <div class="form-actions no-color">
                    <input type="submit" value="Unlink" class="btn btn-default"> |
                    <a href="/Admin/LinkedAccounts">Back to List</a>
                </div>
            </form>
        </div>
        <br/>
    </div>
@endsection