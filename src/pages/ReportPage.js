import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { createItem } from '../services/api';
import '../styles/report.css';
export default function ReportPage() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        status: 'lost',
        image: '',
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({
            ...prev,
            [name]: value,
        }));
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        setError('');
        if (!formData.title.trim() || !formData.description.trim()) {
            setError('Please fill in all required fields');
            return;
        }
        try {
            setLoading(true);
            await createItem(formData);
            navigate('/');
        }
        catch (err) {
            setError('Failed to report item. Please try again.');
            console.error(err);
        }
        finally {
            setLoading(false);
        }
    };
    return (_jsx("div", { className: "report-page", children: _jsxs("div", { className: "report-container", children: [_jsx("h2", { children: "Report a Lost or Found Item" }), _jsxs("form", { onSubmit: handleSubmit, className: "report-form", children: [error && _jsx("div", { className: "error-message", children: error }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "title", children: "Item Name *" }), _jsx("input", { id: "title", type: "text", name: "title", placeholder: "e.g., Red Wallet, Black Keys", value: formData.title, onChange: handleChange, required: true })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "description", children: "Description *" }), _jsx("textarea", { id: "description", name: "description", placeholder: "Provide detailed information about the item...", rows: 5, value: formData.description, onChange: handleChange, required: true })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "status", children: "Status" }), _jsxs("select", { id: "status", name: "status", value: formData.status, onChange: handleChange, children: [_jsx("option", { value: "lost", children: "Lost Item" }), _jsx("option", { value: "found", children: "Found Item" })] })] }), _jsxs("div", { className: "form-group", children: [_jsx("label", { htmlFor: "image", children: "Image URL" }), _jsx("input", { id: "image", type: "url", name: "image", placeholder: "https://example.com/image.jpg", value: formData.image, onChange: handleChange })] }), _jsx("button", { type: "submit", className: "btn-submit", disabled: loading, children: loading ? 'Reporting...' : 'Report Item' })] })] }) }));
}
