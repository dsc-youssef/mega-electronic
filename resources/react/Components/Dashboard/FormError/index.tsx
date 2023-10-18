// Dependencies
import { FC } from "react";

// Types
import { FormErrorProps } from "@/types/Components/Dashboard/FormError";

const FormError: FC<FormErrorProps> = ({ message, className }) => {
  return (
    message && <p className={`form-error ${className}`}>{message}</p>
  )
}

export default FormError;
