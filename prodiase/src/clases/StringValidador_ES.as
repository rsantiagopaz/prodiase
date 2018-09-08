package clases
{
	import mx.validators.StringValidator;

	public class StringValidador_ES extends StringValidator
	{
		public function StringValidador_ES()
		{
			super();
			this.requiredFieldError = "Este campo es obligatorio.";
			this.tooShortError = "Este campo es demasiado corto.";
			this.tooLongError = "Este campo es demasiado largo.";
		}
		
	}
}