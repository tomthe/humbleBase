// filepath: /simplebase-js/simplebase-js/src/types.js
export interface Token {
    value: string;
    createdAt: Date;
}

export interface Column {
    cname: string;
    type: string;
}

export interface Table {
    tablename: string;
    columns: Column[];
}

export interface NewRow {
    cname: string;
    value: any;
}

export interface QueryData {
    query: string;
    tablename: string;
    newdata?: NewRow | NewRow[];
    where?: string;
    columns?: string[];
}