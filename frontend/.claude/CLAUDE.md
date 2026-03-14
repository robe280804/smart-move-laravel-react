# Form structure

All forms must use Zod schemas.
Structure the form schema into the directory src\components\forms.
Set the type of the forms in the filesrc\types\forms.ts
Forms must always use Zod validation.

## Example good form schema

```ts
import { z } from "zod";
import { GENDERS, EXPERIENCE_LEVELS } from "@/constants/const";

export const fitnessInfoSchema = z.object({
  height: z.coerce
    .number()
    .positive("Height must be a positive number.")
    .max(300, "Height must not exceed 300 cm.")
    .multipleOf(0.01, "Maximum 2 decimal places allowed."),

  weight: z.coerce
    .number()
    .positive("Weight must be a positive number.")
    .max(500, "Weight must not exceed 500 kg.")
    .multipleOf(0.01, "Maximum 2 decimal places allowed."),

  age: z.coerce
    .number()
    .int("Age must be a whole number.")
    .positive("Age must be a positive number.")
    .max(120, "Age must not exceed 120."),

  gender: z.enum(GENDERS, {
    message: "Please select a valid gender.",
  }),

  experience_level: z.enum(EXPERIENCE_LEVELS, {
    message: "Please select a valid experience level.",
  }),
});

export type FitnessInfoFormData = z.infer<typeof fitnessInfoSchema>;
export type FitnessInfoFormErrors = Partial<
  Record<keyof FitnessInfoFormData, string>
>;
```

# API to backend

Always check the file backend/routes/api.php for the routes
All API responses must be typed.
All API calls must go through a central API service layer.
Never hardcode backend routes. Always reference the backend route structure.

#
