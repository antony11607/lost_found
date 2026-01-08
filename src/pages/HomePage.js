import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import { getItems } from '../services/api';
import ItemCard from '../components/ItemCard';
import '../styles/home.css';
export default function HomePage() {
    const [items, setItems] = useState([]);
    const [loading, setLoading] = useState(true);
    const [filter, setFilter] = useState('all');
    useEffect(() => {
        loadItems();
    }, [filter]);
    const loadItems = async () => {
        try {
            setLoading(true);
            const response = await getItems(filter !== 'all' ? filter : undefined);
            setItems(response);
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsx("div", { className: "home-page", children: _jsxs("div", { className: "home-container", children: [_jsxs("section", { className: "hero", children: [_jsx("h2", { children: "Find Lost Items or Report Found Ones" }), _jsx("p", { children: "Help reunite people with their belongings" })] }), _jsxs("div", { className: "filters", children: [_jsx("button", { className: `filter-btn ${filter === 'all' ? 'active' : ''}`, onClick: () => setFilter('all'), children: "All Items" }), _jsx("button", { className: `filter-btn ${filter === 'lost' ? 'active' : ''}`, onClick: () => setFilter('lost'), children: "Lost" }), _jsx("button", { className: `filter-btn ${filter === 'found' ? 'active' : ''}`, onClick: () => setFilter('found'), children: "Found" })] }), loading ? (_jsx("div", { className: "loading", children: "Loading items..." })) : items.length === 0 ? (_jsx("div", { className: "empty-state", children: _jsxs("p", { children: ["No items found. ", filter === 'all' && _jsx(Link, { to: "/report", children: "Report one!" })] }) })) : (_jsx("div", { className: "items-grid", children: items.map(item => (_jsx(ItemCard, { item: item }, item.id))) }))] }) }));
}
