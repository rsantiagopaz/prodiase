package clases
{
	import mx.validators.StringValidator;

	public class StringValidador_ES extends StringValidator
	{
		public function StringValidador_ES()
		{
			super();
			this.requiredFieldError = "Este Campo es Obligatorio";
			this.tooShortError = "Este campo es demasiado Corto";
			this.tooLongError = "Este campo es demasiado Largo";
		}
		
	}
}