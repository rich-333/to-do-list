import axios from 'axios';

interface Task {
    id?: number;
    titulo: string;
    descripcion?: string;
    estado: 'pendiente' | 'en_progreso' | 'completada';
    prioridad?: 'baja' | 'media' | 'alta';
    fecha_limite?: string;
    subtareas?: any[];
    etiquetas?: string[];
}

const API_BASE_URL = '/api/v1/tasks';

export const getTasks = async () => {
    try {
        const response = await axios.get(API_BASE_URL);
        return response.data;
    } catch (error) {
        console.error('Error fetching tasks:', error);
        throw error;
    }
};

export const getTask = async (id: number) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/${id}`);
        return response.data;
    } catch (error) {
        console.error('Error fetching task:', error);
        throw error;
    }
};

export const createTask = async (task: Task) => {
    try {
        const response = await axios.post(API_BASE_URL, task);
        return response.data;
    } catch (error) {
        console.error('Error creating task:', error);
        throw error;
    }
};

export const updateTask = async (id: number, task: Partial<Task>) => {
    try {
        const response = await axios.put(`${API_BASE_URL}/${id}`, task);
        return response.data;
    } catch (error) {
        console.error('Error updating task:', error);
        throw error;
    }
};

export const deleteTask = async (id: number) => {
    try {
        const response = await axios.delete(`${API_BASE_URL}/${id}`);
        return response.data;
    } catch (error) {
        console.error('Error deleting task:', error);
        throw error;
    }
};

export const toggleTaskStatus = async (id: number, estado: string) => {
    try {
        const response = await axios.patch(`${API_BASE_URL}/${id}/status`, {
            estado,
        });
        return response.data;
    } catch (error) {
        console.error('Error toggling task status:', error);
        throw error;
    }
};
