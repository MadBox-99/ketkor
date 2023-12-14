<div>
    @includeIf(data_get($setUp, 'footer.includeViewOnTop'))
    @if (filled(data_get($setUp, 'footer.perPage')) &&
            count(data_get($setUp, 'footer.perPageValues')) > 1 &&
            blank(data_get($setUp, 'footer.pagination')))
        <footer
            class="mt-50 w-100 align-items-end d-flex justify-content-sm-center justify-content-md-between flex-wrap px-1 pb-1">
            <div class="my-sm-2 my-md-0 ms-sm-0 col-auto overflow-auto">
                @if (filled(data_get($setUp, 'footer.perPage')) && count(data_get($setUp, 'footer.perPageValues')) > 1)
                    <div class="d-flex flex-lg-row align-items-center">
                        <label class="w-auto">
                            <select class="form-select" wire:model.live="setUp.footer.perPage">
                                @foreach (data_get($setUp, 'footer.perPageValues') as $value)
                                    <option value="{{ $value }}">
                                        @if ($value == 0)
                                            {{ trans('livewire-powergrid::datatable.labels.all') }}
                                        @else
                                            {{ $value }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <small class="text-muted ms-2">
                            {{ trans('livewire-powergrid::datatable.labels.results_per_page') }}
                        </small>
                    </div>
                @endif
            </div>
            <div class="mt-sm-0 col-auto mt-2 overflow-auto">
                @if (method_exists($data, 'links'))
                    {!! $data->links(data_get($setUp, 'footer.pagination') ?: powerGridThemeRoot() . '.pagination', [
                        'recordCount' => data_get($setUp, 'footer.recordCount'),
                    ]) !!}
                @endif
            </div>
        </footer>
    @endif
    @includeIf(data_get($setUp, 'footer.includeViewOnBottom'))
</div>
