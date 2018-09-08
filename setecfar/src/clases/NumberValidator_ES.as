package clases
{
	import mx.validators.NumberValidator;

	public class NumberValidator_ES extends NumberValidator
	{
		public function NumberValidator_ES()
		{
			super();
			this.decimalSeparator = ".";
			this.thousandsSeparator = ",";
			this.requiredFieldError = "Este Campo es Obligatorio";
			this.precisionError = "Solo Deben ir 2 digitos decimales";
			this.negativeError = "El valor no debe ser Negativo";
			this.lowerThanMinError = "El valor es menor al Permitido";
			this.invalidFormatCharsError = "El formato no es valido";
			this.invalidCharError = "El valor posee caracteres no permitidos";
			this.integerError = "El numero debe ser Entero";
			this.exceedsMaxError = "El numero es mayor al Permitido";
			this.decimalPointCountError = "La Separador decimal solo puede Aparece una vez";
			this.separationError = "Despues del Punto (Separador de Cientos) deben ir tres digitos";
			this.precision = 2;
		}
		
	}
}