@extends('layouts.platform')

@section('content')
    @php($headerTab = 'overview')
    @if ($errors->getBag('paper')->any() || session('status_paper'))
        @php($headerTab = 'create')
    @endif

    @php($authorInputs = old('authors', [['name' => '', 'order' => 1]]))

    <div class="section-intro bg-white rounded-4 p-4 p-lg-5 mb-4 border" data-x-data="sectionTabs('{{ $headerTab }}')">
        <div class="d-md-flex align-items-start justify-content-between mb-4">
            <div>
                <div class="nmtafe-kicker text-danger fw-semibold mb-3">Research Papers</div>
                <h2 class="display-6 fw-bold mb-3 text-dark">Knowledge sharing and publication tracking.</h2>
                <p class="lead text-secondary mb-0">Review recent publications and add new research when ready.</p>
            </div>

            <ul class="nav nav-pills mt-4 mt-md-0 p-1 rounded-3 flex-nowrap" id="paperHeaderTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border" id="paper-overview-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#paper-overview-pane" data-x-on:click="activate('overview')"
                        data-x-bind:class="activeTab === 'overview' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="paper-overview-pane"
                        data-x-bind:aria-selected="activeTab === 'overview'">Overview</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="paper-create-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#paper-create-pane" data-x-on:click="activate('create')"
                        data-x-bind:class="activeTab === 'create' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="paper-create-pane" data-x-bind:aria-selected="activeTab === 'create'">
                        <i class="bi bi-journal-text me-1"></i> Create Paper
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="btn btn-sm border ms-2 text-nowrap" id="paper-manage-tab" type="button" role="tab"
                        data-bs-toggle="tab" data-bs-target="#paper-manage-pane" data-x-on:click="activate('manage')"
                        data-x-bind:class="activeTab === 'manage' ? 'btn-danger' : 'btn-light text-danger fw-bold'"
                        aria-controls="paper-manage-pane" data-x-bind:aria-selected="activeTab === 'manage'">
                        <i class="bi bi-sliders me-1"></i> Manage Papers
                    </button>
                </li>
            </ul>
        </div>
