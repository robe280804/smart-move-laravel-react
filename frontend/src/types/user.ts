import type { ExperienceLevel, Gender } from "@/constants/const"


export interface FitnessInfo {
    id: number
    user_id: number
    height: number
    weight: number
    age: number
    gender: Gender
    experience_level: ExperienceLevel
}

export interface StoreFitnessInfoData {
    height: number
    weight: number
    age: number
    gender: Gender
    experience_level: ExperienceLevel
}

export interface UpdateFitnessInfoData {
    height?: number
    weight?: number
    age?: number
    gender?: Gender
    experience_level?: ExperienceLevel
}