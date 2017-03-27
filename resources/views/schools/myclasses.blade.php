@extends('layouts.app')

@section('content')
    <div class="row schools sections">
        <div class="tophero">
            <div class="col-md-8">
                <div class="secondnav">
                    <a href="/schools"> All Schools</a> > {{$school->displayName}}
                </div>
                <div class="a-heading">Classes</div>
            </div>
            <div class="toptiles">
                <div class="section-school-name">{{$school->displayName}}</div>
                <div class="infocontainer">
                    <div class="infoheader">PRINCIPAL</div>
                    <div class="infobody" title="{{$school->principalName}}">
                        @if($school->principalName)
                            {{$school->principalName}}
                        @else
                             -
                        @endif

                    </div>
                </div>
                <div class="infocontainer">
                    <div class="infoheader">Grade levels</div>
                    <div class="infobody" title="{{$school->lowestGrade}}-{{$school->highestGrade}}">
                        {{$school->lowestGrade}}-{{$school->highestGrade}}
                    </div>
                </div>
            </div>
            <div>
                <div class="col-md-4 usericon">
                    <div class="icon"></div>
                    @if($me->educationObjectType === "Student" )
                        <div>Not Enrolled</div>
                    @else
                        <div>Not Teaching</div>
                    @endif
                    <div class="icon my-class"></div><div>My Class</div>
                </div>
                <div class="col-md-3 filterlink-container">
                    <div class="search-container "></div>
                    <span>FILTER:</span> <a id="filtermyclasses" class="filterlink selected" data-type="myclasses">My Classes</a> |
                    <a id="filterclasses" class="filterlink " href="{{url('/allclasses')}}">All Classes</a>
                </div>
            </div>
            <br style="clear:both;" />
        </div>
    </div>
@endsection
