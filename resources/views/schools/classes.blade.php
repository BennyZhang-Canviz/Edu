@extends('layouts.app')

@section('content')
    <?php
    use App\Config\Roles;

    ?>
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
                    <a id="filterclasses" class="filterlink " data-type="allclasses">All Classes</a>
                </div>
            </div>
            <br style="clear:both;" />
        </div>
        <div class="myclasses-container tiles-root-container">
            <div id="allclasses" class="tiles-secondary-container">
                <div class="section-tiles">
                    @if(count($allClasses->value)==0)
                        <div class="nodata"> No classes in this school.</div>
                    @else
                        <div class="content clearfix">
                            @foreach($allClasses->value as $class)
                                <div class="tile-container">
                                    @if($class->IsMySection)
                                        <a class="mysectionlink" href="{{url('/classdetails/'.$school->objectId.'/'.$class->objectId)}}">
                                    @endif
                                    <div class="tile">
                                        <h5>{{$class->DisplayName}}</h5>
                                        <h2>{{$class->CombinedCourseNumber()}}</h2>
                                    </div>
                                    @if($class->IsMySection)
                                        </a>
                                    @endif
                                        <div class="detail">
                                            <h5>Course Id:</h5>
                                            <h6>{{$class->CourseId}}</h6>
                                            <h5>Description:</h5>
                                            <h6>{{$class->CourseDescription}}</h6>
                                            <h5>Teachers:</h5>
                                            @if($class->Users)
                                                @foreach($class->Users as $user)
                                                    @if($user->educationObjectType==='Teacher')
                                                        <h6>{{$user->displayName}}</h6>
                                                    @endif
                                                @endforeach
                                            @endif
                                            <h5>Term Name:</h5>
                                            <h6>{{$class->TermName}}</h6>
                                            <h5>Start/Finish Date:</h5>
                                            <h6>
                                                <span id="termdate">
                                                    <?php
                                                    if(isset($class->TermStartDate))
                                                    {
                                                        $time = strtotime($class->TermStartDate);
                                                        echo date("F d Y",$time);
                                                    }
                                                    ?>
                                                   </span>
                                                <span> - </span>
                                                <span id="termdate">
                                                    <?php
                                                    if(isset($class->TermEndDate))
                                                    {
                                                        $time = strtotime($class->TermEndDate);
                                                        echo date("F d Y",$time);
                                                    }
                                                    ?>
                                                   </span>
                                            </h6>
                                            <h5>Period:</h5>
                                            <h6>{{$class->Period}}</h6>
                                        </div>
                                </div>
                            @endforeach
                        </div>
                        @if(isset($allClasses->skipToken))
                        <div class="seemore " id="see-more">
                            <input id="nextlink" type="hidden" value="{{$allClasses->skipToken}}" />
                            <input id="schoolid" type="hidden" value="{{$school->objectId}}" />
                            <span>See More</span>
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            <div id="myclasses" class="tiles-secondary-container">
                <div class="section-tiles">
                    @if(count($myClasses)===0)
                        @if($me->userRole ===Roles::Faculty)
                            <div class="nodata"> Not teaching any classes.</div>
                        @else
                            <div class="nodata"> Not enrolled in any classes.</div>
                        @endif
                     @else
                        <div class="content clearfix">
                            @foreach($myClasses as $myClass)
                                <div class="tile-container">
                                    <a class="mysectionlink" href="{{url('/classdetails/'.$school->objectId.'/'.$myClass->objectId)}}">
                                    <div class="tile">
                                        <h5>{{$myClass->DisplayName}}</h5>
                                        <h2>{{$myClass->CombinedCourseNumber()}}</h2>
                                    </div>
                                    </a>
                                    <div class="detail">
                                        <h5>Course Id:</h5>
                                        <h6>{{$myClass->CourseId}}</h6>
                                        <h5>Description:</h5>
                                        <h6>{{$myClass->CourseDescription}}</h6>
                                        <h5>Teachers:</h5>
                                          @foreach($myClass->Users as $user)
                                           @if($user->educationObjectType==='Teacher')
                                               <h6>{{$user->displayName}}</h6>
                                            @endif
                                           @endforeach
                                            <h5>Term Name:</h5>
                                            <h6>{{$myClass->TermName}}</h6>
                                            <h5>Start/Finish Date:</h5>
                                            <h6>
                                                <span id="termdate">
                                                    <?php
                                                    if(isset($myClass->TermStartDate))
                                                        {
                                                            $time = strtotime($myClass->TermStartDate);
                                                            echo date("F d Y",$time);
                                                        }
                                                    ?>
                                                   </span>
                                                <span> - </span>
                                                <span id="termdate">
                                                    <?php
                                                    if(isset($myClass->TermEndDate))
                                                    {
                                                        $time = strtotime($myClass->TermEndDate);
                                                        echo date("F d Y",$time);
                                                    }
                                                    ?>
                                                   </span>
                                            </h6>
                                            <h5>Period:</h5>
                                            <h6>{{$myClass->Period}}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('/public/js/sections.js') }}"></script>
@endsection
