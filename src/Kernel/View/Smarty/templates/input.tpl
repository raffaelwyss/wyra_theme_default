<div class="form-group">
    <label class="col-sm-4 control-label text-label" for="{$id}">{$label}</label>
    <div class="col-sm-8">
        <div>
            <input class="form-control" type="{$type}" id="{$id}" name="{$name}" placeholder="{$placeholder}"  ng-required="{$required}" ng-disabled="{$disabled}" ng-readonly="{$readonly}" ng-model="formData.{$name}" />
        </div>
    </div>
</div>