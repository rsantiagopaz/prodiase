package clases
{
	import mx.validators.DateValidator;

	public class DateValidator_ES extends DateValidator
	{
		public function DateValidator_ES()
		{
			super();
			this.requiredFieldError = "Este campo es obligatorio.";
		}
	}
}