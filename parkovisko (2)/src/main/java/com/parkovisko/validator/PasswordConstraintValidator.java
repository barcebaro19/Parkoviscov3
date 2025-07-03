package com.parkovisko.validator;

import jakarta.validation.ConstraintValidator;
import jakarta.validation.ConstraintValidatorContext;
import org.passay.*;

import java.util.Arrays;
import java.util.List;

public class PasswordConstraintValidator implements ConstraintValidator<ValidPassword, String> {

    @Override
    public boolean isValid(String password, ConstraintValidatorContext context) {
        PasswordValidator validator = new PasswordValidator(Arrays.asList(
            // Longitud entre 8 y 30 caracteres
            new LengthRule(8, 30),
            // Al menos una mayúscula
            new CharacterRule(EnglishCharacterData.UpperCase, 1),
            // Al menos una minúscula
            new CharacterRule(EnglishCharacterData.LowerCase, 1),
            // Al menos un número
            new CharacterRule(EnglishCharacterData.Digit, 1),
            // Al menos un carácter especial
            new CharacterRule(EnglishCharacterData.Special, 1),
            // No espacios en blanco
            new WhitespaceRule()
        ));

        RuleResult result = validator.validate(new PasswordData(password));
        if (result.isValid()) {
            return true;
        }

        List<String> messages = validator.getMessages(result);
        String messageTemplate = String.join(",", messages);
        context.buildConstraintViolationWithTemplate(messageTemplate)
               .addConstraintViolation()
               .disableDefaultConstraintViolation();
        return false;
    }
} 