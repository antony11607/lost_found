import { jsx as _jsx, jsxs as _jsxs } from "react/jsx-runtime";
import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import { getItem, getComments, createComment } from '../services/api';
import '../styles/item-detail.css';
export default function ItemDetailPage() {
    const { id } = useParams();
    const [item, setItem] = useState(null);
    const [comments, setComments] = useState([]);
    const [loading, setLoading] = useState(true);
    const [commentText, setCommentText] = useState('');
    const [submitting, setSubmitting] = useState(false);
    useEffect(() => {
        if (id) {
            loadData();
        }
    }, [id]);
    const loadData = async () => {
        try {
            setLoading(true);
            const [itemData, commentsData] = await Promise.all([
                getItem(Number(id)),
                getComments(Number(id)),
            ]);
            setItem(itemData);
            setComments(commentsData);
        }
        finally {
            setLoading(false);
        }
    };
    const handleCommentSubmit = async (e) => {
        e.preventDefault();
        if (!commentText.trim() || !id)
            return;
        try {
            setSubmitting(true);
            await createComment(Number(id), commentText);
            setCommentText('');
            loadData();
        }
        finally {
            setSubmitting(false);
        }
    };
    if (loading) {
        return _jsx("div", { className: "loading", children: "Loading..." });
    }
    if (!item) {
        return (_jsx("div", { className: "item-detail", children: _jsxs("div", { className: "container", children: [_jsx("p", { children: "Item not found" }), _jsx(Link, { to: "/", children: "Back to Browse" })] }) }));
    }
    return (_jsx("div", { className: "item-detail", children: _jsxs("div", { className: "container", children: [_jsx(Link, { to: "/", className: "back-link", children: "\u2190 Back" }), _jsxs("div", { className: "detail-content", children: [_jsxs("div", { className: "detail-image", children: [_jsx("img", { src: item.image || 'https://images.pexels.com/photos/3454496/pexels-photo-3454496.jpeg', alt: item.title }), _jsx("span", { className: `status-badge status-${item.status}`, children: item.status === 'lost' ? 'Lost Item' : 'Found Item' })] }), _jsxs("div", { className: "detail-info", children: [_jsx("h1", { children: item.title }), _jsx("p", { className: "date", children: new Date(item.created_at).toLocaleDateString('en-US', {
                                        month: 'long',
                                        day: 'numeric',
                                        year: 'numeric',
                                    }) }), _jsxs("div", { className: "description", children: [_jsx("h3", { children: "Description" }), _jsx("p", { children: item.description })] })] })] }), _jsxs("div", { className: "comments-section", children: [_jsx("h3", { children: "Comments & Tips" }), _jsxs("form", { onSubmit: handleCommentSubmit, className: "comment-form", children: [_jsx("textarea", { placeholder: "Share information or tips about this item...", value: commentText, onChange: e => setCommentText(e.target.value), rows: 4 }), _jsx("button", { type: "submit", disabled: submitting || !commentText.trim(), children: submitting ? 'Posting...' : 'Post Comment' })] }), _jsx("div", { className: "comments-list", children: comments.length === 0 ? (_jsx("p", { className: "no-comments", children: "No comments yet. Be the first to comment!" })) : (comments.map(comment => (_jsxs("div", { className: "comment", children: [_jsx("p", { children: comment.content }), _jsx("span", { className: "comment-date", children: new Date(comment.created_at).toLocaleDateString() })] }, comment.id)))) })] })] }) }));
}
