@extends('layouts.app')

@section('content')
    <div class="row schools sections">
        <div class="tophero">
            <div class="col-md-8">
                <div class="secondnav">
                    <a href="/schools"> All Schools</a> >
                </div>
                <div class="a-heading">Classes</div>
            </div>
            <div class="toptiles">
                <div class="section-school-name">@Model.School.Name</div>
                <div class="infocontainer">
                    <div class="infoheader">PRINCIPAL</div>
                    <div class="infobody" title="@Model.School.PrincipalName">
                        @Html.Raw(string.IsNullOrEmpty(Model.School.PrincipalName) ? "-" : Model.School.PrincipalName)
                    </div>
                </div>
                <div class="infocontainer">
                    <div class="infoheader">Grade levels</div>
                    <div class="infobody" title="@Model.School.LowestGrade - @Model.School.HighestGrade">
                        @Model.School.LowestGrade - @Model.School.HighestGrade
                    </div>
                </div>
            </div>
            <div>
                <div class="col-md-4 usericon">
                    <div class="icon"></div>

                        <div>Not Enrolled</div>


                        <div>Not Teaching</div>

                        <div class="icon my-class"></div><div>My Class</div>
                </div>
                <div class="col-md-3 filterlink-container">
                    <div class="search-container "></div>
                    <span>FILTER:</span> <a id="filtermyclasses" class="filterlink selected" data-type="myclasses">My Classes</a> | <a id="filterclasses" class="filterlink " data-type="allclasses">All Classes</a>
                </div>
            </div>
            <br style="clear:both;" />
        </div>
    </div>
@endsection
