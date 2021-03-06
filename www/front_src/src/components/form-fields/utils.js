import pick from "lodash/fp/pick";

export const prepareInputProps = pick([
  "type",
  "name",
  "value",
  "checked",
  "disabled",
  "id",
  "placeholder",
  "autoComplete",
  "autoFocus",
  "multiple",
  "required",
  "step",
  "max",
  "min",
  "rows",
  "pattern",
  "maxlength",
  "onFocus",
  "onChange",
  "onInput",
  "onBlur",
  "onClick",
  "style",
  "defaultValue",
  "readonly"
]);
