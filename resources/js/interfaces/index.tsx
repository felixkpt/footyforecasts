export interface Items {
    current_page: number
    data: GameInterface[]
    first_page_url: string
    from: number
    last_page: number
    last_page_url: string
    links: Link[]
    next_page_url: string
    path: string
    per_page: number
    prev_page_url: any
    to: number
    total: number
}


export interface Link {
    url?: string
    label: string
    active: boolean
}

export interface GameInterface {
    id: string
    date_time: string
    date: string
    time: string
    has_time: number
    home_team_id: string
    away_team_id: string
    ht_results: string
    ft_results: string
    competition_abbreviation: string
    competition_id: string
    update_status: number
    update_failed_attempts: number
    url: string
    stadium_id: string
    temperature: string
    weather_condition_id: string
    user_id: string
    status: number
    created_at: string
    updated_at: string
    home_team: string
    away_team: string
}

export interface TeamInterface {
    id: string;
    name: string;
    slug: string;
    games: GameInterface[]
}
