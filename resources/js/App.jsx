import React, { useState, useEffect } from "react";
import { getUsers } from "../js/api/Users"; 

export function App() {
  const [users, setUsers] = useState([]); 

  useEffect(() => {
    async function fetchUsers() {
      try {
        const userData = await getUsers();
        setUsers(userData);
      } catch (e) {
        console.error("Error al obtener los usuarios:", e);
      } 
    }
    
    fetchUsers();
    
  }, []);

  return (
    <div>
        <ul>
          {users.map((user) => (
            <li key={user.id_usuario}>
              Hola {user.nombre}
            </li>
          ))}
        </ul>
    </div>
  );
}