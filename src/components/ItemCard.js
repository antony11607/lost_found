import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { Link } from 'react-router-dom';
import '../styles/item-card.css';
export default function ItemCard({ item }) {
    const formatDate = (date) => {
        return new Date(date).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };
    return (_jsxs(Link, { to: `/item/${item.id}`, className: "item-card", children: [_jsxs("div", { className: "item-image", children: [_jsx("img", { src: item.image || 'https://images.pexels.com/photos/3454496/pexels-photo-3454496.jpeg', alt: item.title }), _jsx("span", { className: `status-badge status-${item.status}`, children: item.status === 'lost' ? 'Lost' : 'Found' })] }), _jsxs("div", { className: "item-content", children: [_jsx("h3", { children: item.title }), _jsxs("p", { className: "description", children: [item.description.substring(0, 100), "..."] }), _jsx("p", { className: "date", children: formatDate(item.created_at) })] })] }));
}
