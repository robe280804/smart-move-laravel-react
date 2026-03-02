export type ApiFieldErrors = Record<string, string[]>;

export class ApiError extends Error {
    public readonly statusCode?: number;
    public readonly fieldErrors?: ApiFieldErrors;

    constructor(
        message: string,
        statusCode?: number,
        fieldErrors?: ApiFieldErrors,
    ) {
        super(message);
        this.name = 'ApiError';
        this.statusCode = statusCode;
        this.fieldErrors = fieldErrors;
    }
}